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
        // TODO: Implement _create() method.
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

        $updated = false;
        if (isset($this->args['text'])) {
            $stmtArgs = array(
                $this->entityId,
                $this->args['text']);
            $this->_noResult('CALL users.sp_update_user_description(?,?)','is',$stmtArgs);
            $updated = true;
        }

        if (!$updated){
            ERR_MISSING_PARAMS();
        }
    }

    protected function _delete()
    {
        // TODO: Implement _delete() method.
    }
}