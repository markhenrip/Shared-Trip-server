<?php
	$con = mysqli_connect("localhost","root","mysqlparool123","sharedtrip");
    if (mysqli_connect_errno())
        echo "Failed to connect to MySQL: " . mysqli_connect_error();

    if (isset($_POST['upl'])) {
        $img = strip_tags($_POST['img']);
        $sql = "CALL sp_temp_upload_image(?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('s', $img);

        if (!$stmt->execute())
            echo ' MySql error: '.$stmt->error;

        $stmt->close();
        $con->close();
    }
    else if (isset($_POST['dwl'])) {
        $id = strip_tags($_POST['id']);
        $sql = "CALL sp_temp_download_image(?)";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $id);

        if (!$stmt->execute())
            echo ' MySql error: '.$stmt->error;

        $result = $stmt->get_result();
        $output = $result->fetch_array(MYSQLI_NUM);
        $stmt->close();
        $con->close();
        echo json_encode($output);
    }
?>