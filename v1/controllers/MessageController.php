<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 28.11.2017
 * Time: 22:16
 */

class MessageController extends ApiControllerBase
{
    protected function _create()
    {
        if (!isset($this->args['message'])) ERR_MISSING_PARAMS();

        $sender = $this->args['sender_id'];
        $topic = $this->args['topic'];
        $time = $this->args['time_sent'];

        if ($time == "") $time = null;
        if ($topic == "") $topic = null;
        if ($sender == "") $sender = null;

        return $this->_easyFetch(
            "CALL messages.sp_new_message(?,?,?,?)",
            "ssss",
            array(
                $this->args['message'],
                $topic,
                $sender,
                $time)
        )[0];

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