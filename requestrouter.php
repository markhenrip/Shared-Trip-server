<?php
    header('Content-Type:Application/json');

	$method = $_SERVER['REQUEST_METHOD'];


/*if ($method=='PUT'){
        $input = file_get_contents('php://input');
        parse_str($input, $put_params);

        echo json_encode($put_params);

        if (!isset($put_params['hdl'])) {
            echo json_encode(array('ERROR', "Missing handler name"));
            exit();
        }

        $hdl = $put_params['hdl'];
        $action = isset($put_params['act'])
            ? $put_params['act']
            : '';
    }

    else {*/
    if (!isset($_REQUEST['hdl'])) {
        echo json_encode(array('ERROR', "Missing handler name"));
        exit();
    }
    $hdl = $_REQUEST['hdl'];
    $action = isset($_REQUEST['act'])
        ? $_REQUEST['act']
        : '';
    $handler = null;

    //echo "handler:".$hdl."; action:".$action;

    $con = mysqli_connect("localhost", "root", "mysqlparool123", "sharedtrip");

    if (mysqli_connect_errno()) {
        echo json_encode(array('ERROR', "Failed to connect to MySQL: " . mysqli_connect_error()));
        exit();
    }

    try {
        $handler = null;
        switch ($hdl) {
            case 'event':
                include 'StEvents.php';
                $params = $_REQUEST;
                $handler = new stEvents($con);
                break;
            case 'user':
                include 'StUser.php';
                $params = $_REQUEST;
                $handler = new StUser($con);
                break;
            case 'admin':
                include 'StEventAdmin.php';
                $params = $_REQUEST;
                $handler = new StEventAdmin($con);
                break;
            default:
                echo json_encode(array('ERROR', 'Handler not defined'));
                exit();
        }

        $response = $handler->handle($method, $action, $params);

        if ($response[1] == 'json')
            echo json_encode($response);
        else
            echo json_encode(array('ERROR', $response));

    } catch (Exception $e) {
        echo json_encode(array('ERROR','Error in communicationg with handler'));
        exit();
    }
?>