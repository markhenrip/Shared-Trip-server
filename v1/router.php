<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 20:42
 */
include  'controllers/ApiControllerBase.php';
include 'controllers/TestController.php';
include  'utils/utils.php';

$uriArgs = $_REQUEST['request'];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD']=='PUT')
    echo json_encode(PUTparams());
if ($_SERVER['REQUEST_METHOD']=='DELETE')
    echo json_encode($_REQUEST);
if ($_SERVER['REQUEST_METHOD']=='POST')
    echo json_encode($_POST);
if ($_SERVER['REQUEST_METHOD']=='GET')
    echo json_encode($_GET);
?>