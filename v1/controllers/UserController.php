<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 18.11.2017
 * Time: 20:24
 */

class UserController extends ApiControllerBase
{

    protected function _create()
    {
        $this->_mustHave('name');
        $this->_mustHaveAny(array('fb_id', 'google_id'));

        $query = null;
        $id = null;

        if (isset($this->args['fb_id'])) {
            $id = $this->args['fb_id'];
            $query = 'CALL users.sp_create_fb_user(?,?,?,?)';
        }

        elseif (isset($this->args['google_id'])) {
            $id = $this->args['google_id'];
            $query = 'CALL users.sp_create_google_user(?,?,?,?)';
        }

        $name = $this->args['name'];
        $picUri = $this->args['picture'];
        $sex = $this->_parseForNull($this->args['gender']);

        return $this->_easyFetch(
            $query,
            'ssss',
            array($id, $name, $sex, $picUri))[0];
    }

    protected function _read()
    {
        switch ($this->verb) {

            case 'conversation':
                $this->_mustHaveAll(array('event', 'from'));
                return null;

            case 'exists':
                $this->_mustHaveAny(array('fb_id', 'google_id'));

                $googleId = $this->_parseForNull($this->args['google_id']);
                $facebookId = $this->_parseForNull($this->args['fb_id']);

                return $this->_easyFetch(
                    'CALL users.sp_user_exists(?,?)',
                    'ss',
                    array($googleId, $facebookId)
                )[0];

            case 'fb':
                $this->_mustHave('userId');
                return $this->_easyFetch(
                    'CALL sp_get_fb_user_data(?)',
                    's',
                    $this->args['userId'])[0];

            case 'browse':
                $this->_mustHaveID();
                return $this->_easyFetch(
                    'CALL sp_get_all_events_for_user(?)',
                    'i',
                    $this->entityId,
                    true,
                    10);

            case 'status':
                $this->_mustHaveID();
                $this->_mustHave('event');

                return $this->_easyFetch(
                    'CALL sp_get_approval_status(?,?)',
                    'ii',
                    array($this->args['event'], $this->entityId)
                )[0];

            case 'joined':
                $this->_mustHaveID();

                return $this->_easyFetch(
                    'CALL sp_get_joined_events(?)',
                    'i',
                    $this->entityId,
                    true,
                    10);

            case null:
                $this->_mustHaveID();

                return $this->_easyFetch(
                    'CALL sp_get_user_data(?)',
                    'i',
                    $this->entityId)[0];

            default:
                ERR_VERB($this->verb);
        }
        return null;
    }

    protected function _update()
    {
        $this->_mustHaveID();

        // NB! can't update name, gender, FB/Google id and picture because they all come from FB/Google

        $this->_mustHaveAny(array('description', 'birthday'));
        $stmtArgs = array($this->entityId);

        if (isset($this->args['description'])) {
            $stmtArgs[] = $this->args['description'];
            $this->_noResult('CALL users.sp_update_user_description(?,?)','is',$stmtArgs);
            array_pop($stmtArgs);
        }

        if (isset($this->args['birthday'])) {
            $stmtArgs[] = $this->args['birthday'];
            $this->_noResult('CALL users.sp_update_user_birthday(?,?)','is', $stmtArgs);
            array_pop($stmtArgs);
        }
    }

    protected function _delete()
    {
        $this->_mustHaveID();
        $this->_mustHave('event');

        $result = $this->_easyFetch(
            'CALL sp_leave_event(?,?)',
            'ii',
            array($this->args['event'], $this->entityId)
        )[0];

        if (isset($result['error_reason'])){
            throw new Exception($result['error_reason']);
        }
        return $result;
    }
}