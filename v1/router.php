<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 20:42
 */
include  'controllers/ApiControllerBase.php';
//include 'controllers/TestController.php';
include 'controllers/EventController.php';
include 'controllers/UserController.php';
include 'controllers/AdminController.php';
include 'controllers/MessageController.php';

include 'utils/parsing.php';
include 'utils/errors.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");


$requestPath = $_REQUEST['request'];
$method = $_SERVER['REQUEST_METHOD'];
$basicParams = array();

/* PUT might contain a file. And then it's *just* the file */
$putFile = preg_match('~/upload~', $requestPath);

try {
    switch ($method) {
        case 'GET':
            $basicParams = $_GET;
            break;
        case 'PUT':
            if (!$putFile) $basicParams = PUTparams();
            break;
        case 'POST':
        case 'DELETE':
            $basicParams = $_REQUEST;
            break;
        default:
            ERR_HTTP_METHOD($method);
    }

    $basicParams['method'] = $method;

    /* for POST requests only */
    if (isset($_FILES['file'])) {

        $basicParams['file'] = $_FILES['file'];
    }
    $pathArgs = parsePathForArgs($requestPath);

    $allArgs = array_merge($basicParams, $pathArgs);

    unset($allArgs['request']);

    $response = null;
    $controllerName = $allArgs['controller'];

    switch ($controllerName) {
        /*case 'test':
            $controller = new TestController($allArgs);
            break;*/
        case 'event':
            $controller = new EventController($allArgs);
            break;
        case 'user':
            $controller = new UserController($allArgs);
            break;
        case 'admin':
            $controller = new AdminController($allArgs);
            break;
        case 'message':
            $controller = new MessageController($allArgs);
            break;
        default:
            ERR_CONTROLLER_NAME($controllerName);
    }

    $response = $controller->process();

} catch (Exception $e) {
    $response = array('error' => $e->getMessage());
}

if (isset($response))
    echo json_encode($response);


?>