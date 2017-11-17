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
            case 'GET':
                return $this->_read();
            case 'POST':
                return $this->_create();
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
     * @param $types - string of types for binding
     * @param $params - regular array of values for binding
     * @return array|null - query results (named)
     * @throws Exception - statement could not execute
     */
    protected function _easyFetch($query, $types = '', $params = null, $withImage = false, $imageIndex = 0) {

        $stmt = $this->connection->prepare($query);

        if (is_array($params)) {

            for ($i = 0; $i < strlen($types); $i++) {
                $stmt->mbind_param($types[$i], $params[$i]);
            }

        } else {
            $stmt->mbind_param($types, $params);
        }

        return $this->_fetch($stmt, $withImage, $imageIndex);
    }

    /** Executes an already prepared statement and returns the result. Can handle images if used correctly.
     * @param mysqli_stmt $stmt
     * @param bool $withImage
     * @param int $imageIndex
     * @return array|null - query results (named)
     */
    protected function _fetch(mysqli_stmt $stmt, $withImage = false, $imageIndex = 0) {
        if (!$stmt->execute()){
            $this->connection->close();
            ERR_STMT_EXEC($stmt->error);
        }

        $result = $stmt->get_result();
        if ($result==null) return null;

        $output = array();

        if ($withImage) {

            $keys = array_map(create_function('$o', 'return $o->name;'), $result->fetch_fields());

            while ($row = $result->fetch_array(MYSQLI_NUM)) {

                if (substr($row[$imageIndex], 0, 4) != "http")
                    $row[$imageIndex] = base64_encode($row[$imageIndex]);

                $output[] = array_combine($keys, $row);
            }

        } else {

            while ($row = $result->fetch_assoc()) {

                $output[] = $row;
            }
        }

        $stmt->close();
        $this->connection->close();

        return $output;
    }
}