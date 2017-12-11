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
        $this->_mustHaveID();

        switch ($this->verb) {
            case 'events':
                return $this->_easyFetch(
                    'CALL sp_get_admin_events(?)',
                    'i',
                    $this->entityId,
                    true,
                    10);

            case 'pending':
                $this->_mustHave('event');

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

        $this->_mustHaveAll(array('event', 'user'));
        switch ($this->verb) {
            case 'pass-rights':
                $this->_mustHaveID();
                $result = $this->_easyFetch('CALL events.sp_change_admin(?,?,?)',
                    'iii',
                    array($this->entityId, $this->args['event'], $this->args['user'])
                );
                if (isset($result) and count($result)>0 and key_exists('error',$result[0])) {
                    return $result[0];
                }
                $result = $this->_easyFetch(
                    'CALL sp_leave_event(?,?)',
                    'ii',
                    array($this->args['event'], $this->args['user'])
                )[0];

                if (isset($result['error_reason'])) throw new Exception($result['error_reason']);
                return $result;

            case 'approve':
                return $this->_easyFetch(
                    'CALL sp_approve_user(?,?)',
                    'ii',
                    array(
                        $this->args['event'],
                        $this->args['user']));
            case 'reject':
                return $this->_easyFetch(
                    'CALL events.sp_reject_user(?,?)',
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