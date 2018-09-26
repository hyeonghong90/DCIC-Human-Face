<?php
ini_set('display_errors', 1);
require 'credentials.inc.php';
$connect = pg_connect('host=' . DBHOST . ' dbname=' .DBNAME . ' user=' .DBUSER . ' password=' . DBPASS);
if (!$connect){
	die("Error in connection!");
}
$action = $_GET['action'];
if ($action == "retrieve") {
	$sql = "SELECT * FROM humanface.parcels ORDER BY parcel_id";
}
elseif ($action == "edit") {
	$parcel_num = intval($_GET['p_num']);
	$block_num = intval($_GET['b_num']);
	$sql = "SELECT * FROM humanface.parcels WHERE block_no = '$block_num' and parcel_no = '$parcel_num' ORDER BY parcel_id";
}
$result = pg_query($connect, $sql);
if(!$result){
	die("Error in SQL query: " . pg_last_error());
}
$rows = pg_num_rows($result);
if ($rows == 0) {
	echo "Zero result";
} else {
	if ($action == "retrieve") {
		while ($row = pg_fetch_array($result)) {
			$data = array(
				"parcel_id" => $row['parcel_id'],
				"block_no" => $row['block_no'],
				"parcel_no"=> $row['parcel_no'],
				"ward_no"=> $row['ward_no'],
				"land_use"=> $row['land_use']		
			);
			$datas[] = $data;
		}
		echo json_encode($datas);			
	}
	elseif ($action == "edit") {
		while ($row = pg_fetch_array($result)) {
			$data = array(
				// "st_num" => $row['st_num'],
				// "st_name" => $row['st_name'],
				// "parcel_no"=> $row['parcel_no'],
				// "block_no"=> $row['block_no'],
				// "date"=> $row['date'],
				// "type" => $row['type'],
				// "response" => $row['response']		

				"parcel_no"=> $row['parcel_no'],
				"block_no"=> $row['block_no']
			);
			$datas[] = $data;
		}
		echo json_encode($datas);			
	
	}
}
pg_free_result($result);
pg_close($connect);
?>