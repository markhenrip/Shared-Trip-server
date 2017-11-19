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
        if (!isset($this->args['user'], $this->args['location'],
            $this->args['name'], $this->args['description'],
            $this->args['total_cost'], $this->args['spots'],
            $this->args['start_date'], $this->args['end_date'],
            $this->args['private'])
        ) ERR_MISSING_PARAMS();

        if ($this->args['start_date'] == "null") $this->args['start_date'] = null;
        if ($this->args['end_date']   == "null") $this->args['end_date']   = null;

        $sql = 'CALL sp_event_creation(?,?,?,?,?,?,?,?,?,?)';
        $usr = $this->args['user'];
        $loc = $this->args['location'];
        $name = $this->args['name'];
        $cost = $this->args['total_cost'];
        $spots = $this->args['spots'];
        $desc = $this->args['description'];
        $start = $this->args['start_date'];
        $end = $this->args['end_date'];
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
        if (!isset($this->entityId)){
            ERR_MISSING_PARAMS();
        }

        return $this->_easyFetch(
            'CALL sharedtrip.sp_get_event(?)',
            'i',
            $this->entityId,
            true,
            10
        )[0]; // makes sense to return an element, not an array of one element
    }

    protected function _update()
    {
        if ($this->verb == 'upload'){
            return $this->_updateFile();
        }

        $updated = false;
        if (isset($this->args['name'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['name']);
            $this->_noResult('CALL events.sp_update_event_name(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['location'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['location']);
            $this->_noResult('CALL events.sp_update_event_location(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['admin'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['admin']);
            $this->_noResult('CALL events.sp_update_event_admin(?,?)','ii',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['total_cost'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['total_cost']);
            $this->_noResult('CALL events.sp_update_event_cost(?,?)','ii',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['spots'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['spots']);
            $this->_noResult('CALL events.sp_update_event_spots(?,?)','ii',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['description'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['description']);
            $this->_noResult('CALL events.sp_update_event_description(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['start_date'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['start_date']);
            $this->_noResult('CALL events.sp_update_event_start_date(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['end_date'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['end_date']);
            $this->_noResult('CALL events.sp_update_event_end_date(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['private'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['private']);
            $this->_noResult('CALL events.sp_update_event_privacy(?,?)','ii',$stmtArgs);
            $updated = true;
        }

        if (!$updated){
            ERR_MISSING_PARAMS();
        }
    }

    protected function _delete()
    {
        if (!isset($this->entityId)){
            ERR_MISSING_PARAMS();
        }

        $this->_noResult('CALL events.sp_delete_event(?)','i',$this->entityId);
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
}