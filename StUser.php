<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 11.11.2017
 * Time: 15:25
 */

class StUser
{
    private $connection;
    private function ACTION_NAME_ERR($action_name) { return 'Action not defined for this handler and method: '.$action_name; }
    private function METHOD_NAME_ERR($method_name) { return 'Method not defined for this handler: '.$method_name; }
    private function MISSING_PARAMS_ERR() { return "Missing one or more query parameters"; }
    private function QUERY_EXEC_ERR($error_text) { return 'Could not execute query because: '.$error_text; }

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
            case '':
                return $this->_getOwnData($params);
            case 'fb':
                return $this->_getDataViaFbId($params);
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _routePost($action, $params) {
        switch ($action) {
            case '':
                return $this->_register($params);
            case 'desc':
                return $this->_updateDescription($params);
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

    private function _getDataViaFbId($params) {
        if (!isset($params['user']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $id = strip_tags($params['user']);
            $sqlstring = "CALL sp_get_fb_user_data(?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("s", $id);

            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $result = $stmt->get_result()->fetch_array(MYSQLI_NUM);

            $stmt->close();
            $this->connection->close();

            if ($result==null)
                return 'User not found';

            return array("SUCCESS", "json", $result);

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }

    private function _getOwnData($params){
        if (!isset($params['user']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $id = strip_tags($params['user']);
            $sqlstring = "CALL sp_get_user_data(?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $result = $stmt->get_result()->fetch_array(MYSQLI_NUM);

            $stmt->close();
            $this->connection->close();

            if ($result==null)
                return 'User not found';

            return array("SUCCESS", "json", $result);

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }

    private function _register($params) {
        if (!isset($params['fb_id'], $params['name']
            , $params['gender'], $params['birth_date']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $id = strip_tags($params['fb_id']);
            $name = strip_tags($params['name']);
            $sex = strip_tags($params['gender']);
            $bd = strip_tags($params['birth_date']);
            if ($bd=='null')
                $bd=null;
            if (!isset($params['picture'])) {
                $sqlstring = "CALL sp_register_or_update_fb_user(?,?,?,?,null)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("ssss", $id, $name, $sex, $bd);
            }
            else {
                $pic = strip_tags($params['picture']);
                $sqlstring = "CALL sp_register_or_update_fb_user(?,?,?,?,?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("sssss", $id, $name, $sex, $bd, $pic);
            }

            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $result = $stmt->get_result()->fetch_array(MYSQLI_NUM);

            $stmt->close();
            $this->connection->close();
            return array("SUCCESS", "json", $result);

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }

    private function _updateDescription ($params) {

        if (!isset($params['user'], $params['text']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $id = strip_tags($params['user']);
            $desc = strip_tags($params['text']);
            $sqlstring = "CALL sp_update_user_description(?,?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("is", $id, $desc);

            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $stmt->close();
            $this->connection->close();
            return array("SUCCESS", "json");

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }
}