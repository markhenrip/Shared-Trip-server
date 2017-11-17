<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 20:44
 */

class TestController extends ApiControllerBase
{
    public function __construct($allArgs)
    {
        parent::__construct($allArgs);
    }

    public function canIReachGlobals() {
        return $_REQUEST;
    }

    protected function _create()
    {
        // TODO: Implement _create() method.
    }

    protected function _read()
    {
        switch ($this->verb) {
            case 'first':
                return $this->_easyFetch('CALL sp_get_event(?)', 'i', array(1));
            default:
                return $this->_easyFetch('CALL sp_get_all_events()');
        }
    }

    protected function _update()
    {
        // TODO: Implement _update() method.
    }

    protected function _delete()
    {
        // TODO: Implement _delete() method.
    }
}