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
        define( 'API_ACCESS_KEY', 'AIzaSyD95xHr9r5NYIO8nm3XBsFwsmUcxHBzxu0' );

        $this->_mustHaveAll(array('message', 'event', 'sender_id', 'topic'));

        $message = $this->args['message'];
        $sender = $this->args['sender_id'];
        $topic = $this->args['topic'];
        $time = $this->_parseForNull($this->args['time_sent']);
        $event = $this->args['event'];

        $messageSaved = $this->_easyFetch(
            "CALL messages.sp_new_message(?,?,?,?,?)",
            "ssisi",
            array(
                $message,
                $topic,
                $sender,
                $time,
                $event)
        )[0];

        if (!isset($messageSaved['message_id']) || !is_numeric($messageSaved['message_id'])) {
            throw new Exception(
                "Invalid internal message id: " . $messageSaved['message_id']
            );
        }

        if ($time == null) $time = $messageSaved['time_sent_utc'];

        $data = array(
            "to" => "/topics/" . $topic,
            "data" => array(
                "sender_id" => $sender,
                "time" => $time,
                "message" => $message,
                "event_id" => $event
            )
        );

        $data_string = json_encode( $data );

        $headr = array(
            'Content-type: application/json',
            'Content-Length: ' . strlen($data_string),
            'Authorization: key='.API_ACCESS_KEY
        );

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headr );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, $data_string );
        $fcmResult = json_decode(curl_exec($ch));
        curl_close( $ch );

        if (isset($fcmResult->error))
            return $fcmResult;

        $messageUpdated = $this->_easyFetch(
            "CALL messages.sp_message_received_by_fcm(?,?)",
            "is",
            array(
                $messageSaved['message_id'],
                $fcmResult->message_id."")
        )[0];

        $messageUpdated['sender_picture'] = $messageSaved['sender_picture'];
        $messageUpdated['sender_name'] = $messageSaved['sender_name'];

        return $messageUpdated;
    }

    protected function _read()
    {
        ERR_MISSING_FUNCTION_READ($this->entityName);
    }

    protected function _update()
    {
        $this->_mustHaveID();
        $this->_mustHave('user');
        $this->_noResult(
            'CALL messages.sp_mark_as_seen(?,?)',
            'ii',
            array(
                $this->entityId,
                $this->args['user'])
        );
    }

    protected function _delete()
    {
        ERR_MISSING_FUNCTION_DELETE($this->entityName);
    }
}