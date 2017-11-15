<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 13.11.2017
 * Time: 19:40
 */

include 'TestObject.php';
$arr = explode("/",$_REQUEST['request']);
$obj = new TestObject(json_encode($arr));
echo $obj->trySomething();

/*
$target_file = $_FILES["file"];
$con = mysqli_connect("localhost","root","mysqlparool123","sharedtrip");
$sql = "CALL sp_temp_upload_image(?)";
$stmt = $con->prepare($sql);
$null = NULL;
$stmt->bind_param('b', $null);
$fp = fopen($target_file["tmp_name"], "r");
while (!feof($fp)) {
    $stmt->send_long_data(0, fread($fp, 8192));
}
fclose($fp);
if (!$stmt->execute()) {
        echo ' MySql error: '.$stmt->error;
	exit();
}
$stmt->close();

$sql = "SELECT content FROM sharedtrip.temp_image_upload ORDER BY id DESC LIMIT 1";
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->get_result()->fetch_array()[0];
header('Content-Type:text/plain');
$stmt->close();
$con->close();

echo base64_encode($result);
*/

	
/*
while ($row = mysqli_fetch_assoc($result)) {
	$array[] = $row;
}
header('Content-Type:Application/json');

echo json_encode($array);
*/

/*
$hdr = $_SERVER['HTTP_CUSTOM'];
header('Content-Type: image/png');
echo $hdr.' ';
*/
//print_r (explode("/",$_REQUEST['request']))
//print_r(file_get_contents('data:image/png;base64,'.base64_encode($target_file)));
//echo '<img src="'.$target_file["name"].'">';
?>