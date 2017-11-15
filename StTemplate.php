<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 9.11.2017
 * Time: 22:13
 */

/*class StTemplate
{
    private $connection;
    private function ACTION_NAME_ERR($action_name) { return 'Action not defined for this handler and method: '.$action_name; }
    private function METHOD_NAME_ERR($method_name) { return 'Method not defined for this handler: '.$method_name; }
    private function QUERY_EXEC_ERR($error_text) { return 'Could not execute query because: '.$error_text; }
    private function MISSING_PARAMS_ERR() { return "Missing one or more query parameters"; }

    public function __construct($con) {
        $this->connection = $con;
    }

    public function handle($method, $action, $params) {
        switch ($method) {
            case 'GET':
                return $this->_routeGet($action, $params);
            case 'POST':
                return $this->_routePost($action, $params);
            case 'PUT':
                return $this->_routePut($action, $params);
            case 'DELETE':
                return $this->_routeDelete($action, $params);
            default:
                return $this->METHOD_NAME_ERR($method);
        }
    }

    private function _routeGet($action, $params) {
        switch ($action) {
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _routePost($action, $params) {
        switch ($action) {
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _routePut($action, $params) {
        switch ($action) {
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _routeDelete($action, $params) {
        switch ($action) {
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }
}*/