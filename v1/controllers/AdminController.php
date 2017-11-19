<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 19.11.2017
 * Time: 15:56
 */

class AdminController extends ApiControllerBase
{

    protected function _create()
    {
        ERR_MISSING_FUNCTION_CREATE($this->entityName);
    }

    protected function _read()
    {
        if (!isset($this->entityId))
            ERR_MISSING_PARAMS();

        switch ($this->verb) {
            case 'events':
                return $this->_easyFetch(
                    'CALL sp_get_admin_events(?)',
                    'i',
                    $this->entityId,
                    true,
                    10);

            case 'pending':
                if (!isset($this->args['event']))
                    ERR_MISSING_PARAMS();

                return $this->_easyFetch(
                    'CALL sp_get_pending_participators(?)',
                    'i',
                    $this->args['event']);

            default:

                ERR_VERB($this->verb);
        }
    }

    protected function _update()
    {
        if (!isset($this->args['event'], $this->args['user']))
            ERR_MISSING_PARAMS();

        switch ($this->verb) {
            case 'approve':
                return $this->_easyFetch(
                    'CALL sp_approve_user(?,?)',
                    'ii',
                    array(
                        $this->args['event'],
                        $this->args['user']));
            case 'ban':
                return $this->_easyFetch(
                    'CALL sp_ban_user(?,?)',
                    'ii',
                    array(
                        $this->args['event'],
                        $this->args['user']));
            default:
                ERR_VERB($this->verb);
        }
    }

    protected function _delete()
    {
        ERR_MISSING_FUNCTION_DELETE($this->entityName);
    }
}