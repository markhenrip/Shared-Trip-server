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
        if (!isset($this->args['fb_id'], $this->args['name'], $this->args['gender']))
            ERR_MISSING_PARAMS();

        $id = $this->args['fb_id'];
        $name = $this->args['name'];
        $sex = $this->args['gender'];
        $picUri = $this->args['picture'];

        return $this->_easyFetch(
            'CALL users.sp_create_fb_user(?,?,?,?)',
            'ssss',
            array($id, $name, $sex, $picUri));
    }

    protected function _read()
    {
        switch ($this->verb) {

            case 'fb':
                if (!isset($this->args['userId']))
                    ERR_MISSING_PARAMS();

                return $this->_easyFetch(
                    'CALL sp_get_fb_user_data(?)',
                    's',
                    $this->args['userId'])[0];

            case null:
                if (!isset($this->entityId))
                    ERR_MISSING_PARAMS();

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
        if (!isset($this->entityId)) {
            ERR_MISSING_PARAMS();
        }

        // NB! can't update name, gender, FB/Google id and picture because they all come from FB/Google

        $updated = false;
        if (isset($this->args['description'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['description']);
            $this->_noResult('CALL users.sp_update_user_description(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (isset($this->args['birthday'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['birthday']);
            $this->_noResult('CALL users.sp_update_user_birthday(?,?)','is', $stmtArgs);
            $updated = true;
        }

        if (!$updated){
            ERR_MISSING_PARAMS();
        }
    }

    protected function _delete()
    {
        ERR_MISSING_FUNCTION_DELETE($this->entityName);
    }
}