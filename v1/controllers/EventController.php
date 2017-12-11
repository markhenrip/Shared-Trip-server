<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 17.11.2017
 * Time: 19:39
 */

class EventController extends ApiControllerBase
{
    /**
     * EventController constructor.
     */
    public function __construct($allArgs)
    {
        parent::__construct($allArgs);
    }

    protected function _create()
    {
        if ($this->verb=='join') {
            return $this->_joinEvent();
        }

        $this->_mustHaveAll(
            array('user', 'name', 'location', 'admin', 'total_cost', 'spots',
                'description', 'start_date', 'end_date', 'private')
        );

        $sql = 'CALL events.sp_create_event(?,?,?,?,?,?,?,?,?,?)';
        $usr = $this->args['user'];
        $loc = $this->args['location'];
        $name = $this->args['name'];
        $cost = $this->args['total_cost'];
        $spots = $this->args['spots'];
        $desc = $this->args['description'];
        $start = $this->_parseForNull($this->args['start_date']);
        $end = $this->_parseForNull($this->args['end_date']);
        $priv = $this->args['private'];

        if (isset($this->file)) {
            $null = null;
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param(
                'issiisssib',
                $usr, $loc, $name, $cost, $spots, $desc, $start, $end, $priv, $null);

            // include a file
            $fp = fopen($this->file["tmp_name"], "r");
            while (!feof($fp)) {
                $stmt->send_long_data(9, fread($fp, 8192));
            }
            fclose($fp);
            return $this->_fetch($stmt);

        } else {
            $stmtArgs = array(
                $usr, $loc, $name, $cost, $spots, $desc, $start, $end, $priv);

            $defaults = array(
                "https://101clipart.com/wp-content/uploads/01/Winter%20Vacation%20Clipart%2007.jpg",
                "http://images.clipartpanda.com/sunset-clipart-tropical_sunset_scene_with_palm_trees_and_birds_0071-1012-0820-2524_SMU.jpg",
                "http://gclipart.com/wp-content/uploads/2017/06/Concert-clip-art-free-hd-vector-gallery.jpg",
                "https://classroomclipart.com/images/gallery/Clipart/Camping/TN_camping-clipart-6227.jpg",
                "http://images.all-free-download.com/images/graphicthumb/carnival_confetti_art_background_vector_581096.jpg",
                "https://tinyclipart.com/resource/sports-clipart/sports-clip-art-sports-clipart.jpg",
                "https://img1.etsystatic.com/108/0/6612289/il_570xN.879576735_7lvx.jpg"
            );
            $i = array_rand($defaults);
            $stmtArgs[] = $defaults[$i];
            return $this->_easyFetch($sql, 'issiisssis', $stmtArgs);
        }
    }

    protected function _read()
    {
        $this->_mustHaveID();
        switch ($this->verb) {
            case "participators": {
                return $this->_easyFetch(
                    'CALL events.sp_get_participators(?)',
                    'i',
                    $this->entityId
                );
            }

            case "image":
                return $this->_easyFetch(
                    'CALL events.sp_get_event_picture(?)',
                    'i',
                    $this->entityId,
                    true,
                    0
                )[0];

            case null:
                return $this->_easyFetch(
                    'CALL sharedtrip.sp_get_event(?)',
                    'i',
                    $this->entityId,
                    true,
                    10
                )[0]; // makes sense to return an element, not an array of one element
            default:
                ERR_VERB($this->verb);
        }
    }

    protected function _update()
    {
        $this->_mustHaveID();

        if ($this->verb == 'upload'){
            return $this->_updateFile();
        }

        // Property name => property type (s for string, i for int, d for double)
        $allEventProperties = array(
            'name' => 's',
            'location' => 's',
            'admin' => 's',
            'total_cost' => 'i',
            'spots' => 'i',
            'description' => 's',
            'start_date' => 's',
            'end_date' => 's',
            'private' => 'i'
        );

        $this->_mustHaveAny(array_keys($allEventProperties));

        foreach ($allEventProperties as $propName => $propType) {
            $this->_updateIfSpecified($propName, $propType);
        }
    }

    protected function _delete()
    {
        $this->_mustHaveID();
        $participator = $this->args['participator'];
        if (isset($participator)) {

            $result = $this->_easyFetch(
                'CALL sp_leave_event(?,?)',
                'ii',
                array($this->entityId, $participator)
            )[0];

            if (isset($result['error_reason'])) throw new Exception($result['error_reason']);
            return $result;
        }
        else {
            $this->_noResult('CALL events.sp_delete_event(?)', 'i', $this->entityId);
        }
    }

    private function _updateFile() {
        $null = null;
        $stmt = $this->connection->prepare('CALL events.sp_update_event_picture(?,?)');
        $id = $this->entityId;
        $stmt->bind_param('ib', $id, $null);
        $putData = fopen("php://input", "rb");

        while ($data = fread($putData, 8192)) {
            $stmt->send_long_data(1, $data);
        }
        fclose($putData);

        if (!$stmt->execute()){
            $this->connection->close();
            ERR_STMT_EXEC($stmt->error);
        }

        $stmt->close();
        $this->connection->close();
    }

    private function _joinEvent()
    {
        $this->_mustHave('user');

        $message = $this->args['message'];
        $myFriend = $this->args['friend'];

        if (isset($message)) {
            $this->_noResult(
                'CALL events.sp_join_event(?,?,?)',
                'iis',
                array($this->entityId, $this->args['user'], $message));
        }

        elseif (isset($myFriend)) {
            $result = $this->_easyFetch(
                'CALL events.sp_join_friend_event(?,?,?)',
                'iis',
                array($this->entityId, $this->args['user'], $myFriend.''));
            if (isset($result) and count($result)>0 and array_key_exists('error', $result[0])) {
                return $result[0];
            }
        }

        else {

            $this->_noResult(
                'CALL sp_join_event(?,?)',
                'ii',
                array($this->entityId, $this->args['user']));
        }
    }

    private function _updateIfSpecified($propertyName, $type) {
        if (isset($this->args[$propertyName])) {
            $query = 'CALL events.sp_update_event_' . $propertyName . '(?,?)';

            $stmtArgs = array(
                $this->entityId,
                $this->args[$propertyName]
            );

            $this->_noResult(
                $query,
                'i' . $type,
                $stmtArgs);
        }
    }
}