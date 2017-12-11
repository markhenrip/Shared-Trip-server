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
                $this->_mustHaveID();
                $this->_mustHave('event');

                return $this->_easyFetch(
                    'CALL messages.sp_message_history(?,?,?)',
                    'iii',
                    array(
                        $this->entityId, $this->args['event'], $this->_valueOrZero('from')
                    ));

            case 'my-events':
                $this->_mustHaveID();
                return $this->_easyFetch(
                    'CALL events.sp_get_my_events(?)',
                    'i',
                    $this->entityId,
                    true,
                    10
                );

            case 'unread':
                $this->_mustHaveID();
                return $this->_getUnreadSorted();

            case 'friend-events':
                $this->_mustHave("data");

                return $this->_fetchObscured(
                    "v_vc50_ft",
                    "ufi",
                    $this->args["data"],
                    array("fb_id","user_id","full_name","first_name","user_picture","event_id","event_name","location","event_picture"),
                    $this->args['after'],
                    $this->args['max'],
                    true,
                    8);

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
                    'CALL users.sp_get_not_ended_events_for_user(?)',
                    'i',
                    $this->entityId,
                    true,
                    10);

            case "search":
                return $this->_search();

            case 'status':
                $this->_mustHaveID();
                $this->_mustHave('event');

                return $this->_easyFetch(
                    'CALL sharedtrip.sp_get_approval_status(?,?)',
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
                    'CALL users.sp_get_user_info(?)',
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
        ERR_MISSING_FUNCTION_DELETE($this->entityName);
    }

    private function _search() {

        $this->_mustHaveID();
        $this->_mustHaveAny(array('name', 'location'));
        $combinedResult = array();

        if (isset($this->args['name'])) {
            $result = $this->_easyFetch(
                'CALL events.sp_search_event_by_name(?,?)',
                'si',
                array($this->args['name'], $this->entityId),
                true,
                10);

            $combinedResult = $result;
        }

        /*if (isset($this->args['location'])) {
            $result = $this->_easyFetch(
                'CALL search.sp_search_events_by_location(?)',
                's',
                $this->args['location']);

            $combinedResult = $combinedResult
                ? $result
                : array_intersect($result, $combinedResult);
        }*/

        return array_values($combinedResult);
    }

    private function _getUnreadSorted() {
        $allUnreads =  $this->_easyFetch(
            'CALL messages.sp_all_unread(?)',
            'i',
            $this->entityId
        );

        $sorted = array();
        foreach ($allUnreads as $unreadMessage) {

            $eventId = $unreadMessage['event_id'];
            $orderNr = $this->_elementWithFieldExists($sorted, 'event', $eventId);

            $eventMessages = null;
            if ($orderNr > -1){
                $eventMessages = $sorted[$orderNr]['messages'];
                $eventMessages[] = array(
                    'from' => $unreadMessage['sender_id'],
                    'time_sent' => $unreadMessage['time_fcm_received_utc'],
                    'text' => $unreadMessage['message'],
                    'id' => $unreadMessage['message_id']
                );
                $sorted[$orderNr]['messages'] = $eventMessages;
            }
            else {
                $eventObject = array(
                    'event' => $eventId,
                    'event_name' =>  $unreadMessage['event_name'],
                    'topic' => $unreadMessage['topic_name'],
                    'messages' => []
                );

                $eventMessages[] = array(
                    'from' => $unreadMessage['sender_id'],
                    'time_sent' => $unreadMessage['time_fcm_received_utc'],
                    'text' => $unreadMessage['message'],
                    'id' => $unreadMessage['message_id']
                );
                $eventObject['messages'] = $eventMessages;
                $sorted[] = $eventObject;
            }
        }
        return $sorted;
    }

    private function _elementWithFieldExists($array, $fieldName, $expectedValue) {
        $count = 0;
        foreach ($array as $element) {
            if (array_key_exists($fieldName, $element) and $element[$fieldName] == $expectedValue)
                return $count;

            $count++;
        }
        return -1;
    }
}