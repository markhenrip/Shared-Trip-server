<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 9.11.2017
 * Time: 22:16
 */
class StEventAdmin
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
            default:
                return $this->METHOD_NAME_ERR($method);
        }
    }

    private function _routeGet($action, $params) {
        switch ($action) {
            case '':
                return $this->_events($params);
            case 'pnd':
                return $this->_pending($params);
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _routePost($action, $params) {
        switch ($action) {
            case 'apr':
                return $this->_approve($params);
            case 'rej':
                return $this->_ban($params);
            default:
                return $this->ACTION_NAME_ERR($action);
        }
    }

    private function _pending($params) {
        if (!isset($params['event']))
            return $this->MISSING_PARAMS_ERR();
        try {
            $event = strip_tags($params['event']);

            $sqlstring = "CALL sp_get_pending_participators(?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("i", $event);

            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $result = $stmt->get_result();
            if ($result==null)
                return array("SUCCESS", "json", null);

            $output = array();
            while ($row = $result->fetch_assoc())
            {
                if (substr($row['event_picture'], 0, 4)!="http")
                    $row['event_picture']=base64_encode($row['event_picture']);
                $output[] = $row;
            }
            $stmt->close();
            $this->connection->close();

            return array("SUCCESS", "json", $output);

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }

    /**
     * @param $params - must include participator(user id) and event(id)
     * @return array|string - 'SUCCESS' message
     */
    private function _approve($params) {

        if (!isset($params['participator'], $params['event']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $user = $params['participator'];
            $event = $params['event'];

            $sqlstring = "CALL sp_approve_user(?,?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("ii", $event, $user);

            if (!$stmt->execute()) {
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

    /**
     * @param $params - must include participator(user id) and event(id)
     * @return array|string - 'SUCCESS' message
     */
    private function _ban($params) {
        if (!isset($params['participator'], $params['event']))
            return $this->MISSING_PARAMS_ERR();

        try {
            $user = $params['participator'];
            $event = $params['event'];

            $sqlstring = "CALL sp_ban_user(?,?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("ii", $event, $user);

            if (!$stmt->execute()) {
                $stmt->close();
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

    private function _events($params) {
        if (!isset($params['user']))
            return $this->MISSING_PARAMS_ERR();
        try {
            $id = strip_tags($params['user']);
            $sqlstring = "CALL sp_get_admin_events(?)";
            $stmt = $this->connection->prepare($sqlstring);
            $stmt->bind_param("i", $id);


            if (!$stmt->execute()){
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($stmt->error);
            }

            $result = $stmt->get_result();
            if ($result==null)
                return 'Events not found';

            $output = array();
            while ($row = $result->fetch_assoc())
            {
                if (substr($row['event_picture'], 0, 4)!="http")
                    $row['event_picture']=base64_encode($row['event_picture']);
                $output[] = $row;
            }
            $stmt->close();
            $this->connection->close();

            return array("SUCCESS", "json", $output);

        } catch (Exception $e) {
            $this->connection->close();
            return $this->QUERY_EXEC_ERR($e->getMessage());
        }
    }
}