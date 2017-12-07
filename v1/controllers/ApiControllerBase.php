<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 18:11
 */

abstract class ApiControllerBase
{
    protected $httpMethod;
    protected $entityName;
    protected $entityId;
    protected $verb;
    protected $file;
    protected $args;
    protected $connection;

    public function __construct($allArgs)
    {
        include 'errors.php';
        include 'statement.php';

        $this->httpMethod = $allArgs['method'];
        $this->entityName = $allArgs['controller'];
        $this->entityId = $allArgs['id'];
        $this->verb = $allArgs['verb'];
        $this->file = $allArgs['file'];

        unset($allArgs['method'], $allArgs['controller'], $allArgs['id'], $allArgs['verb'], $allArgs['file']);

        $this->args = $allArgs;

        $this->connection = new db("localhost", "root", "mysqlparool123", "sharedtrip");

        if (mysqli_connect_errno()) {
            ERR_MYSQLI_CONNECTION(mysqli_connect_error());
        }
    }

    public function testMyProperties() {
        return get_object_vars($this);
    }

    public function process() {
        switch ($this->httpMethod) {
            case 'POST':
                return $this->_create();
            case 'GET':
                return $this->_read();
            case 'PUT':
                return $this->_update();
            default:
                return $this->_delete();
        }
    }

    abstract protected function _create();
    abstract protected function _read();
    abstract protected function _update();
    abstract protected function _delete();


    /** Creates a prepared statement, binds parameters dynamically, executes the statement and returns the result.
     * WARNING: Can't handle files and images!
     * @param $query - parametrized sql query string
     * @param string $types - string of types for binding
     * @param $params - regular array of values for binding
     * @param array $customLabels
     * @param bool $withImage - whether the results contain an image
     * @param int $imageIndex - if they do, then at which position
     * @param bool $debug
     * @return array|null|string - query results (named)
     */
    protected function _easyFetch($query, $types = '', $params = null, $withImage = false, $imageIndex = 0
    , $customLabels = null) {

        $stmt = $this->connection->prepare($query);

        if (is_array($params)) {

            for ($i = 0; $i < strlen($types); $i++) {
                $stmt->mbind_param($types[$i], $params[$i]);
            }

        } else if (isset($params)) {
            $stmt->mbind_param($types, $params);
        }

        return $this->_fetch($stmt, $withImage, $imageIndex, $customLabels);
    }

    /** Executes an already prepared statement and returns the result. Can handle images if used correctly.
     * @param mysqli_stmt $stmt
     * @param array $customLabels
     * @param bool $withImage
     * @param int $imageIndex
     * @return array|null - query results (named)
     */
    protected function _fetch(mysqli_stmt $stmt, $withImage = false, $imageIndex = 0, $customLabels = null) {
        if (!$stmt->execute()){
            $this->connection->close();
            ERR_STMT_EXEC($stmt->error);
        }

        $result = $stmt->get_result();

        return $this->_compileResults($result, $customLabels, $withImage, $imageIndex);
    }

    protected function _noResult($query, $types = '', $params = null) {
        $stmt = $this->connection->prepare($query);

        if (is_array($params)) {

            for ($i = 0; $i < strlen($types); $i++) {
                $stmt->mbind_param($types[$i], $params[$i]);
            }

        } else if (isset($params)) {
            $stmt->mbind_param($types, $params);
        }

        if (!$stmt->execute()){
            $this->connection->close();
            ERR_STMT_EXEC($stmt->error);
        }
    }

    protected function _fetchObscured
    (
        $viewName,
        $argName,
        $encodedParams,
        $customLabels = null,
        $after = null,
        $max = null,
        $withImage = false,
        $imageIndex = 0
    )
    {
        $sql = "SELECT * FROM obscured." . $viewName
            . " WHERE " . $argName
            . " IN ('" . implode("', '", json_decode(base64_decode($encodedParams))) . "')";

        if (isset($max)) {
            $sql .= " LIMIT " . $max;
            if (isset($after)) $sql .= " OFFSET " . $after;
        }

        $result = $this->connection->query($sql);

        return $this->_compileResults($result, $customLabels, $withImage, $imageIndex);
    }

    private function _compileResults(mysqli_result $result, $customLabels = null, $withImage = false, $imageIndex = 0) {

        if ($result==null) return null;

        $fields = $result->fetch_fields();

        if (isset($customLabels) and count($customLabels) != count($fields))
            ERR_RESPONSE_LABELS(count($customLabels));

        $output = array();

        if ($withImage) {

            $keys = isset($customLabels)
                ? $customLabels
                : array_map(create_function('$o', 'return $o->name;'), $fields);

            while ($row = $result->fetch_array(MYSQLI_NUM)) {

                if (substr($row[$imageIndex], 0, 4) != "http")
                    $row[$imageIndex] = base64_encode($row[$imageIndex]);

                $output[] = array_combine($keys, $row);
            }

        } else {

            while ($row = $result->fetch_assoc()) {

                if (isset($customLabels)){
                    $values = array_values($row);
                    $row = array_combine($customLabels, $values);
                }

                $output[] = $row;
            }
        }

        return $output;
    }

    protected function _mustHave($argName) {
        if (!isset($this->args[$argName])) ERR_MISSING_PARAMS($argName);
    }

    protected function _mustHaveAll($argNames) {
        $missing = array();

        foreach ($argNames as $name) {
            if (!isset($this->args[$name]))
                $missing[] = $name;
        }
        if (count($missing) > 0) {
            ERR_MISSING_PARAMS(implode(", ", $missing));
        }
    }

    protected function _mustHaveAny($argNames) {
        foreach ($argNames as $name) {
            if (isset($this->args[$name]))
                return;
        }
        ERR_MISSING_PARAMS(implode(" / ", $argNames));
    }

    protected function _mustHaveID() {
        if (!isset($this->entityId)) ERR_MISSING_PARAMS('<ENTITY_ID>');
    }

    protected function _parseForNull($string) {
        if (isset($string) and ($string == 'null' or $string == '')) return null;
        return $string;
    }

    protected function _valueOrZero($argName) {
        if (!isset($this->args[$argName])) return 0;
        return $this->args[$argName];
    }

    protected function _has($argName) {
        return isset($this->args[$argName]);
    }
}