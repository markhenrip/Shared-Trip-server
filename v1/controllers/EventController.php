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
        ) MISSING_PARAMS_ERR();

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
        ERR_MISSING_FUNCTION_READ($this->entityName);
    }

    protected function _update()
    {
        ERR_MISSING_FUNCTION_UPDATE($this->entityName);
    }

    protected function _delete()
    {
        ERR_MISSING_FUNCTION_DELETE($this->entityName);
    }
}