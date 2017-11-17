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
        include '../utils/statement.php';
        include '../utils/errors.php';

        $this->httpMethod = $allArgs['method'];
        $this->entityName = $allArgs['controller'];
        $this->entityId = $allArgs['id'];
        $this->verb = $allArgs['verb'];
        $this->file = $allArgs['file'];

        unset($allArgs['method'], $allArgs['controller'], $allArgs['id'], $allArgs['verb'], $allArgs['file']);

        $this->args = $allArgs;

        $this->connection = new db("localhost","root","mysqlparool123","sharedtrip");

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


    /**
     * @param $query - parametrized sql query string
     * @param $types - string of types for binding
     * @param $params - regular array of values for binding
     * @return array|null - query results (named)
     * @throws Exception - statement could not execute
     */
    protected function _fetch($query, $types = '', $params = null) {

        $stmt = $this->connection->prepare($query);

        for ($i = 0; $i < strlen($types); $i++) {
            $stmt->mbind_param($types[$i], $params[$i]);
        }

        if (isset($this->file)) {

            // include a file ($null binding param must have already been provided)
            $fp = fopen($this->file["tmp_name"], "r");
            while (!feof($fp)) {
                $stmt->send_long_data($i == 0 ? $i : $i-1, fread($fp, 8192));
            }
            fclose($fp);
        }

        if (!$stmt->execute()){
            $this->connection->close();
            ERR_STMT_EXEC($stmt->error);
        }

        $result = $stmt->get_result();
        if ($result==null) return null;

        $output = array();

        while ($row = $result->fetch_assoc())
        {
            $output[] = $row;
        }

        $stmt->close();
        $this->connection->close();

        return $output;
    }
}