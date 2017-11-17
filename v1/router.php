<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 20:42
 */
include  'controllers/ApiControllerBase.php';
include 'controllers/TestController.php';
include 'utils/parsing.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Content-Type: application/json");

$requestPath = $_REQUEST['request'];
$method = $_SERVER['REQUEST_METHOD'];
$basicParams = null;

try {
    switch ($method) {
        case 'GET':
            $basicParams = $_GET;
            break;
        case 'PUT':
            $basicParams = PUTparams();
            break;
        case 'POST':
        case 'DELETE':
            $basicParams = $_REQUEST;
            break;
        default:
            ERR_HTTP_METHOD($method);
    }

    $basicParams['method'] = $method;

    if (isset($_FILES['file'])) {
        $basicParams['file'] = $_FILES['file'];
    }

    $pathArgs = parsePathForArgs($requestPath);
    $allArgs = array_merge($basicParams, $pathArgs);
    unset($allArgs['request']);

    $response = null;
    $controllerName = $allArgs['controller'];

    switch ($controllerName) {
        case 'test':
            $controller = new TestController($allArgs);
            $response = $controller->process();
            break;
        default:
            ERR_CONTROLLER_NAME($controllerName);
    }

} catch (Exception $e) {
    $response = array('error' => $e->getMessage());
}

echo json_encode($response);


?>