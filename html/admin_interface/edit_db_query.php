<?php
// Query function that creates different query based on its argument
function query_request($q_request, $pid){
	ini_set('display_errors', 1);
	require '../credentials.inc.php';

	//Connection to PostgreSQL
	$connect = pg_connect('host=' . DBHOST . ' dbname=' . DBNAME . ' user=' . DBUSER . ' password=' . DBPASS);
	if (!$connect){
		die("Error in connection!:" . pg_last_error());
	}
	
	if ($q_request == "parcel_request") {
		$query = "SELECT p.parcel_id, p.block_no, p.parcel_no, p.ward_no, p.land_use
			FROM humanface.parcels p
			WHERE parcel_id = '" . $pid . "';";
	} elseif ($q_request == "address_request") {
		$query = "SELECT a.id as \"address_id\", a.st_num, a.st_name
			FROM humanface.addresses a
				WHERE parcel_id = '" . $pid . "';";
	} elseif ($q_request == "event_request") {
		$query = "SELECT e.event_id, e.response, e.extra_information, e.date, e.price, 
					et.id as event_type_id, et.type, epa.role,
					peo.person_id, peo.name
			FROM humanface.parcels p
            	JOIN humanface.events e on p.parcel_id = e.parcel_id
                JOIN humanface.event_types et on e.type = et.id
                JOIN humanface.event_people_assoc epa on e.event_id = epa.event_id
                JOIN humanface.people peo on epa.person_id = peo.person_id
			WHERE p.parcel_id = '" . $pid . "';";
	} elseif($q_request == "name_request") {
		$query = "SELECT name FROM humanface.people;";
	}
	$result = pg_query($connect, $query);

	if(!$result){
		die("Error in SQL query: " . pg_last_error());
	}
	$info = pg_fetch_all($result);
	pg_free_result($result);
	pg_close($connect);
	
	return $info; 
}
?>

