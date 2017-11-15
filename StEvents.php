<?php
	
	class stEvents {

		private $connection;
		private function ACTION_NAME_ERR($action_name) { return 'Action not defined for this handler and method: '.$action_name; }
        private function METHOD_NAME_ERR($method_name) { return 'Method not defined for this handler: '.$method_name; }
        private function QUERY_EXEC_ERR($error_text) { return 'Could not execute query because: '.$error_text; }
        private function MISSING_PARAMS_ERR() { return "Missing one or more query parameters"; }
        private function CRITERIA_NAME_ERR($criteria_name) { return 'Unknown search criteria: '.$criteria_name; }
		
		public function __construct($con) {
			$this->connection = $con;
		}
		
		public function handle($method, $action, $params) {
			switch ($method) {
                case 'GET':
                    return $this->_routeGet($action, $params);
				case 'POST': 
					return $this->_routePost($action, $params);
				default:
					return $this->METHOD_NAME_ERR($method);
			}
		}

        private function _routeGet($action, $params) {
            switch ($action) {
                case '':
                    return $this->_all();
                case 'u':
                    return $this->_one($params);
                case 'apst':
                    return $this->_approval($params);
                case 'wappr':
                    return $this->_allExtra($params);
                case 'search':
                    return $this->_search($params);
                case 'joined':
                    return $this->_getJoined($params);
                default:
                    return $this->ACTION_NAME_ERR($action);
            }
        }

		private function _routePost($action, $params) {
			switch ($action) {
				case '':
					return $this->_create($params);
                case 'join':
                    return $this->_join($params);
                case 'testdel':
                    return $this->_testDeleteEvent($params);
                case 'testleave':
                    return $this->_testDeleteParticipation($params);
				default:
                    return $this->ACTION_NAME_ERR($action);
			}
		}

        private function _join($params) {
            if (!isset($params['participator'], $params['event']))
                return $this->MISSING_PARAMS_ERR();

            try {
                $event = $params['event'];
                $user = $params['participator'];
                $sqlstring = "CALL sp_join_event(?,?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("ii", $event,$user);

                if (!$stmt->execute()) {
                    $stmt->close();
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json");

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        /**
         * @param $params - must include participator(user id) and event(id)
         * @return array|string - 'SUCCESS' message
         */
        private function _leave($params) {
            if (!isset($params['participator'], $params['event']))
                return $this->MISSING_PARAMS_ERR();

            try {
                $user = $params['participator'];
                $event = $params['event'];

                $sqlstring = "CALL sp_leave_event(?,?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_params("ii", $event, $user);

                if (!$stmt->execute()) {
                    $stmt->close();
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();

                $outputName = $result->fetch_fields()[0]['name'];
                $outputValue = $result->fetch_array(MYSQLI_NUM)[0];

                if($outputName == 'error_reason'){
                    $err_reason = $outputValue;
                    switch ($err_reason) {
                        case 'is_banned':
                            return 'All event related operations are suspended due to ban';
                        default:
                            return 'Failed to join event';
                    }
                }

                $stmt->close();
                $this->connection->close();

                $response = array(
                    $outputName => $outputValue
                );

                return array('SUCCESS', 'json', $response);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        /**
         * @param $params - contains the following:
         *   user(int id)
         * , location(str)
         * , name(str)
         * , description(str)
         * , total_cost(int)
         * , spots(id)
         * , start_date(datetime)
         * , end_date(datetime)
         * , private(bool)
         * , picture(BLOB)
         * @return array|string - 'SUCCESS' message
         */
        private function _create($params) {
            if (!isset($params['user']
                ,$params['location'],$params['name']
                ,$params['description'],$params['total_cost']
                ,$params['spots'],$params['start_date']
                ,$params['end_date'],$params['private']))
                return $this->MISSING_PARAMS_ERR();

			try {
                $id = strip_tags($params['user']);
                $loc = strip_tags($params['location']);
                $name = strip_tags($params['name']);
                $desc = strip_tags($params['description']);
                $cost = strip_tags($params['total_cost']);
                $spots = strip_tags($params['spots']);
                $begin = strip_tags($params['start_date']);
                $end = strip_tags($params['end_date']);
                $priv = strip_tags($params['private']);

                if ($begin=="null") $begin=null;
                if($end=="null") $end=null;

                $sqlstring = "CALL sp_event_creation(?,?,?,?,?,?,?,?,?,?)";
                $stmt = $this->connection->prepare($sqlstring);

                if (!isset($_FILES['picture'])) {
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
                    $pic = $defaults[$i];
                    $stmt->bind_param("issiisssis", $id, $loc, $name, $cost,$spots,$desc,$begin, $end, $priv, $pic);
                }
                else {
                    $target_file = $_FILES["picture"];
                    $nullPic = null;
                    $stmt->bind_param("issiisssib", $id, $loc, $name, $cost,$spots,$desc,$begin, $end, $priv, $nullPic);
                    $fp = fopen($target_file["tmp_name"], "r");
                    while (!feof($fp)) {
                        $stmt->send_long_data(9, fread($fp, 8192));
                    }
                    fclose($fp);
                }

				if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
				}
                $result = $stmt->get_result();
                if ($result==null)
                    return array("SUCCESS", "json", null);

                $output = array();
                while ($row = $result->fetch_assoc())
                {
                    $output[] = $row;
                }
                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json", $output);

			} catch (Exception $e) {
				$this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
			}
		}

		private function _one($params) {
            try {
                $event = $params['event'];
                $sql = "CALL sp_get_event(?)";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('i', $event);

                if (!$stmt->execute()) {
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();

                if ($result==null)
                    return array('ERROR', 'no result');

                $output = array();
                while ($row = $result->fetch_array(MYSQLI_NUM))
                {
                    if (substr($row[10], 0, 4)!="http")
                        $row[10]=base64_encode($row[10]);
                    $output[] = $row;

                }

                if (count($output)==0)
                    return array('SUCCESS', 'json', $result);

                $stmt->close();
                $this->connection->close();
                return array('SUCCESS', 'json', $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

		private function _search($params) {
		    if (!isset($params['by']))
		        return 'Missing search criteria';
            if (!isset($params['kw']))
                return 'Missing search keyword';

            $search_criteria = $params['by'];
            $search_keyword = $params['kw'];
            $sql = "";

		    switch ($search_criteria) {
                case 'name':
                    $sql = "CALL sp_search_event_by_name(?)";
                    break;

                default:
                    return $this->CRITERIA_NAME_ERR($search_criteria);
            }

            try {
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param('s', $search_keyword);

                if (!$stmt->execute()) {
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();
                $output = array();
                while ($row = $result->fetch_array(MYSQLI_NUM))
                {
                    if (substr($row[10], 0, 4)!="http")
                        $row[10]=base64_encode($row[10]);
                    $output[] = $row;
                }

                $stmt->close();
                $this->connection->close();
                return array('SUCCESS', 'json', $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _getJoined($params) {
            if (!isset($params['user']))
                return $this->MISSING_PARAMS_ERR();

            try {
                $id = strip_tags($params['user']);
                $sqlstring = "CALL sp_get_joined_events(?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("i", $id);

                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();
                if ($result==null)
                    return 'Events not found';

                $output = array();
                while ($row = $result->fetch_array(MYSQLI_NUM))
                {
                    $output[] = $row;
                }
                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json", $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _all() {
            try {
                $sqlstring = "CALL sp_get_all_events()";
                $stmt = $this->connection->prepare($sqlstring);

                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();
                if ($result==null)
                    return 'Events not found';

                $output = array();
                while ($row = $result->fetch_assoc())
                {
                    $output[] = $row;
                }
                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json", $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _allExtra($params) {
            if (!isset($params['user']))
                return $this->MISSING_PARAMS_ERR();
            try {
                $id = strip_tags($params['user']);
                $sqlstring = "CALL sp_get_all_events_for_user(?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("i", $id);

                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();
                if ($result==null) { return array("SUCCESS", "json", array()); }

                $output = array();
                $i = 0;
                while ($row = $result->fetch_assoc())
                {
                    if (substr($row['event_picture'], 0, 4)!="http")
                        $row['event_picture']=base64_encode($row['event_picture']);
                    $output[] = $row;
                }

                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json", $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _approval($params) {
            if (!isset($params['user'], $params['event']))
                return $this->MISSING_PARAMS_ERR();
            try {
                $id = strip_tags($params['user']);
                $ev = strip_tags($params['event']);
                $sqlstring = "CALL sp_get_approval_status(?,?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("ii", $ev, $id);


                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $result = $stmt->get_result();
                if ($result==null)
                    return array("SUCCESS", "json", null);

                $output = array();
                while ($row = $result->fetch_assoc())
                {
                    $output[] = $row;
                }
                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json", $output);

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _testDeleteParticipation($params) {
            try {
                $id = strip_tags($params['user']);
                $ev = strip_tags($params['event']);
                $sqlstring = "CALL sharedtripTests.sp_delete_participation(?,?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("ii", $ev, $id);


                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json");

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }

        private function _testDeleteEvent($params) {
            try {
                $ev = strip_tags($params['event']);
                $sqlstring = "CALL shredtripTests.sp_delete_event(?)";
                $stmt = $this->connection->prepare($sqlstring);
                $stmt->bind_param("i", $ev);

                if (!$stmt->execute()){
                    $this->connection->close();
                    return $this->QUERY_EXEC_ERR($stmt->error);
                }

                $stmt->close();
                $this->connection->close();

                return array("SUCCESS", "json");

            } catch (Exception $e) {
                $this->connection->close();
                return $this->QUERY_EXEC_ERR($e->getMessage());
            }
        }
	}
?>