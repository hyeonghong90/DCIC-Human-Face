<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link rel="stylesheet" href="main_php.css">
	<link rel="stylesheet" href="auto_complete_form.css">

	<!-- Bootstrap CSS -->
	<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous"> -->

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">

	<title>DCIC Human Face of Big Data</title>

	<?php
	require '../credentials.inc.php';
	include 'edit_db_query.php';
	include 'edit_dropdown_names.php';

	ini_set('display_errors', 1);

	//Connection to PostgreSQL
	$connect = pg_connect('host=' . DBHOST . ' dbname=' . DBNAME . ' user=' . DBUSER . ' password=' . DBPASS);
	if (!$connect){
		die("Error in connection!:" . pg_last_error());
	}

	// processing the passed parcel_id
	$p_id = $_GET["pid"];

	//Query for parcel information
	$query = "SELECT p.parcel_id, p.block_no, p.parcel_no, p.ward_no, p.land_use
			FROM humanface.parcels p
			WHERE parcel_id = '" . $p_id . "';";

	$result = pg_query($connect, $query);
	$parcel_info = pg_fetch_all($result);

	$query = "SELECT a.id as \"address_id\", a.st_num, a.st_name
			FROM humanface.addresses a
				WHERE parcel_id = '" . $p_id . "';";

	$result = pg_query($connect, $query);
	$address_info = pg_fetch_all($result);

	$query = "SELECT e.event_id, e.response, e.extra_information, e.date, e.price, 
					et.id as event_type_id, et.type, epa.id as event_asso_id, epa.role,
					peo.person_id, peo.name
			FROM humanface.parcels p
            	JOIN humanface.events e on p.parcel_id = e.parcel_id
                JOIN humanface.event_types et on e.type = et.id
                JOIN humanface.event_people_assoc epa on e.event_id = epa.event_id
                JOIN humanface.people peo on epa.person_id = peo.person_id
			WHERE p.parcel_id = '" . $p_id . "';";	

	$result = pg_query($connect, $query);
	$event_info = pg_fetch_all($result);

	echo "<pre>";
	print_r($address_info);
	echo "</pre>";

	# Extracting properties
	// $parcel_cols = array_keys($parcel_info[0]);
	// $address_cols = array_keys($address_info[0]);	
	?>

</head>
<body>
	<header>
	<h1>DCIC: Human Face of big Data</h1>
	<p1>Utilize this search page to find parcel specific information within the DCIC: Human Face of Big Data relational database system.</p1>
	</header>


	<div class="container lg-font col-md-12">
		<!-- <form id="edit_form" class="form-horizontal" style="border:0px dotted black;" onsubmit="updateDB()"> -->
			<div class="col-md-12" role="titlebar" id="titlebar">
	      		<div class="section-title"><h3>Parcel Information</h3></div>
	    	</div>
	    	<!-- Basic Parcel Information -->
      		<div class="form-group col-md-3">
      			<!--Iterate the parcel_info array -->
      			<?php $i=0; foreach ($parcel_info[0] as $key => $value){ if($key == 'parcel_id'){continue;} ?>
  				<label for="<?php echo $key?>"><?php echo $key;?><small class="required">*</small></label>
  				<?php if($key != 'land_use') { ?>
        		<input type="text" class="parcel" id="<?php echo 'parcel' . $i;?>" parcel_id=<?php echo $parcel_info[0]['parcel_id'];?> for="<?php echo $key;?>" value = "<?php echo trim($value);?>" required minlength="1">
        		<?php } else { ?>
    			<input list="land-usage" type="text" class="parcel" id="<?php echo 'parcel' . $i;?>" parcel_id="<?php echo $parcel_info[0]['parcel_id'];?>" for="land_use" value="<?php echo trim($parcel_info[0]['land_use']);?>" required minlength="1">
  				<datalist id="land-usage">
  					<option value="residential">
  					<option value="commercial">
  				</datalist> 
  				<?php } $i++; } ?>
  			</div>

      		<!-- Address Information -->
      		<div class="form-group col-md-3">
	        	<?php $index=0; for ($i=0; $i<sizeof($address_info); $i++) { foreach($address_info[$i] as $key => $value) { if ($key == 'address_id') {continue;}?>
        		<label for="<?php echo $key?>"><?php echo $key;?></label>
    			<input type="text" class="form-control" id="<?php echo 'address' . $index;?>" address_id="<?php echo $address_info[$i]['address_id'];?>" for="<?php echo $key;?>" value = "<?php echo trim($value);?>">
	        	<?php $index++;}} ?>
    		</div>

    		<!-- Event Information -->
			<div class="form-group col-md-3">
	    	<?php $index=0; for ($i=0; $i<sizeof($event_info); $i++) {
	    		foreach($event_info[$i] as $key => $value) { 
	    			if ($key == 'event_id' || $key == 'event_type_id' || $key == 'person_id' || $key == 'event_asso_id') {
					continue; }?>
				<div class="col-md-12" role="events" id="role-events">
				<?php if ($key == 'name') { ?>
					<label for="<?php echo $key?>"><?php echo $key;?><small class="required">*</small></label>
					<form autocomplete="off" action="/action_page.php">
					  <div class="autocomplete" style="width:300px;">
					    <input class="namecell" type="text" id="<?php echo 'person' . $index;?>" person_id="<?php echo $event_info[$i]['person_id'];?>" value="<?php echo trim($value);?>">
					  </div>
					</form>
		    	<?php } else { ?>
		        	<label for="<?php echo $key?>"><?php echo $key;?>
		        	<?php if (!($key == 'response' || $key == 'extra_information')) {?>
		        		<small class="required">*</small>
		        	<?php } ?></label>
		        	<?php if ($key == 'role') { ?>
		    		<font color="red">If you want to change person, do not touch role.</font>
		    		<?php } ?>
        			<input type="text" class="form-control" id=
        				<?php if ($key == 'response' || $key == 'extra_information' 
        				|| $key == 'date' || $key == 'price') {
        					echo 'event' . $index . ' ' . 'event_id=' . $event_info[$i]['event_id'];
        				} elseif ($key == 'type') {
        					echo 'event_type' . $index . ' ' . 'event_id=' . $event_info[$i]['event_id'] . ' ' . 'event_type_id=' . $event_info[$i]['event_type_id'];
        				} else if ($key == 'role') {
        					// echo 'event_asso' . $index . ' ' . 'event_asso_id=' . $event_info[$i]['event_asso_id'] . ' ' . 'event_type_id=' . $event_info[$i]['event_type_id'];
        			}?> for="<?php echo $key;?>" value = "<?php echo trim($value);?>" 
    				<?php if (!($key == 'response' || $key == 'extra_information')) {?>
        				required minlength="1" <?php }?>>
        			<?php } ?>
	        	</div>
			<?php $index++;}} ?>
			</div>

			<!-- Submit Button -->
	        <div class="col-md-12" role="submit-titlebar"  id="role-submit-titlebar">
	            <div class="section-title"><h3>Submit this Entry</h3></div>
	        </div>
	        <div class="col-md-12">    
	            <button class="btn btn-primary" type="submit" value="submit" onclick="updateDB();">SUBMIT</button>
	        </div>
	        <div class="col-md-12 form-footer">            
	        </div>
	    <!-- </form> -->
	    <div class="col-md-12">
	    	<div class="required"> * Required</div>
	    </div>
	</div>

	<br><br>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>

<script>
function updateDB(){
	// var ids = ["parcel_id", "address_id", "event_id", "event_type_id", "person_id"];
	var ids = ["event_type_id"];

	for (var i=0; i<ids.length; i++) {
		var input_tags = $("[" + ids[i] + "]");
		if (ids[i] == "parcel_id") {
			// for (var j=0; j<input_tags.length; j++) {
			// 	var id_value=input_tags[j].getAttribute("id");
			// 	// input_tags[j].setAttribute("value", $("[" + ids[i] + "][for='" + col + "']").val());
			// 	input_tags[j].setAttribute("value", $("#" + id_value).val());
			// }
			// var p_key = input_tags[0].getAttribute("parcel_id");
			// var block_no = input_tags[0].getAttribute("value");
			// var parcel_no = input_tags[1].getAttribute("value");
			// var ward_no = input_tags[2].getAttribute("value");
			// var land_use = input_tags[3].getAttribute("value");

			// $.ajax({
			// 	type:"GET",
			// 	async:true,
			// 	url:"edit_db_update.php",
			// 	data: {
			// 		action: 'parcel',
			// 		parcel_id: p_key,
			// 		block_no: block_no,
			// 		parcel_no: parcel_no,
			// 		ward_no: ward_no,
			// 		land_use: land_use
			// 	}
			// })
		} else if (ids[i] == "address_id") {
			// for (var j=0; j<input_tags.length; j++) {
			// 	var id_value=input_tags[j].getAttribute("id");
			// 	// input_tags[j].setAttribute("value", $("[" + ids[i] + "][for='" + col + "']").val());
			// 	input_tags[j].setAttribute("value", $("#" + id_value).val());
			// }
			// for (var k=0; k<input_tags.length; k+=2){

			// 	var p_key = input_tags[k].getAttribute("address_id");
			// 	var st_num = input_tags[k].getAttribute("value");
			// 	var st_name = input_tags[k+1].getAttribute("value");

			// 	if (st_num != null && st_name != null) {
			// 		$.ajax({
			// 			type:"GET",
			// 			async:true,
			// 			url:"edit_db_update.php",
			// 			data: {
			// 				action: 'address',
			// 				address_id: p_key,
			// 				st_num: st_num,
			// 				st_name: st_name
			// 			}
			// 		})
			// 	}
			// }
		} else if (ids[i] == "event_id") {
			// for (var j=0; j<input_tags.length; j++) {
			// 	var id_value=input_tags[j].getAttribute("id");
			// 	// input_tags[j].setAttribute("value", $("#" + id_value).val());
			// 	console.log(input_tags[j]);
			// }
			// for (var k=0; k<input_tags.length; k+=4){
				// var p_key = input_tags[k].getAttribute("event_id");
				// var response = input_tags[k].getAttribute("value");
				// var extra_information = input_tags[k+1].getAttribute("value");
				// var date = input_tags[k+2].getAttribute("value");
				// var price = input_tags[k+3].getAttribute("value");

				// console.log(p_key);
				// console.log(response);
				// console.log(extra_information);
				// console.log(date);
				// console.log(price);
				// console.log("------------");

			// 	if (date != null && price != null) {
					// $.ajax({
					// 	type:"GET",
					// 	async:true,
					// 	url:"edit_db_update.php",
					// 	data: {
					// 		action: 'event',
					// 		event_id: p_key,
					// 		response: response,
					// 		extra_information: extra_information,
					// 		date: date,
					// 		price: price
					// 	}
					// })
			// 	}
			// }
		} else if (ids[i] == "event_type_id") {
			for (let j=0; j<input_tags.length; j++) {
				var id_value=input_tags[j].getAttribute("id");
				// input_tags[j].setAttribute("value", $("[" + ids[i] + "][for='" + col + "']").val());
				input_tags[j].setAttribute("value", $("#" + id_value).val());
				// console.log(input_tags[j]);
				// console.log(input_tags[j].getAttribute("value"));

				$.ajax({
					type:"GET",
					asyne:true,
					url:"edit_db_update.php",
					data: {
						action: 'type_request',
						type: input_tags[j].getAttribute("value")
					},
					dataType: "json"
				}).done(function(data){
					// console.log(data[0]["event_type_id"]);	
					let new_id = data[0]["event_type_id"];
					input_tags[j].setAttribute("event_type_id", new_id);
				})
			}

			for (let k=0; k<input_tags.length; k++){
				var p_key = input_tags[k].getAttribute("event_type_id");
				var e_id = input_tags[k].getAttribute("event_id");

				$.ajax({
					type:"GET",
					async:true,
					url:"edit_db_update.php",
					data: {
						action: 'event_type',
						et_id: p_key,
						e_id: e_id
					}
				})

			}

		} else if (ids[i] == "person_id") {
			// for (var j=0; j<input_tags.length; j++) {
			// 	var id_value=input_tags[j].getAttribute("id");
			// 	// input_tags[j].setAttribute("value", $("[" + ids[i] + "][for='" + col + "']").val());
			// 	input_tags[j].setAttribute("value", $("#" + id_value).val());
			// }
			for (var k=0; k<input_tags.length; k++){
				console.log(input_tags[k]);
			}
		}
	}		
	// var parcel_t = $("input[parcel_id]");
	// var address_t = $("input[address_id]");
	// var event_t = $("input[event_id]");
	// var event_type_t = $("input[event_type_id]");
	// var person_t = $("input[person_id]");

	// console.log(person_t[0]);

	// var multiple_ts = [parcel_t, address_t, event_t, event_type_t, person_t];

	// console.log($("input[person_id]").attr("parcel_id"));

	// console.log($("input[person_id]"));

	// var pid = $("input[parcel_id]").attr("parcel_id");
	// var aid = $("input[address_id]").attr("address_id");
	// var etid = $("input[event_type_id]").attr("event_type_id");
	// var eid = $("input[event_id]").attr("event_id");
	// var pid = $("input[person_id").attr("person_id");

	// for (var i=0; i<multiple_ts.length; i++) {
	// 	for (var j=0; j<multiple_ts[i].length; j++) {
	// 		console.log(multiple_ts[i][j].getAttribute("for") + " " + multiple_ts[i][j].getAttribute("value"));
	// 	}
	// }


	// var address_t = $( "input[address_id]");
	// for (var i=0; i<address_t.length; i++){
	// 	console.log(address_t[i].getAttribute("for") + " " + address_t[i].getAttribute("value"));
	// }
}

<?php
# Storing all names
$query = "SELECT name FROM humanface.people;";
$result = pg_query($connect, $query);
$names = pg_fetch_all($result);

# process names
foreach ($names as $key => $value) {
    foreach ($value as $v) {
        $n[] = trim($v);
    }
}
?>;
// ajax call
var names = <?php echo '["' . implode('", "', array_unique($n)) . '"]' ?>;

/*initiate the autocomplete function on the "myInput" element, and pass along the countries array as possible autocomplete values:*/
var name_cells = document.getElementsByClassName("namecell");
for (var i=0; i<name_cells.length; i++) {
	autocomplete(name_cells[i], names);
}

//funtion filter(){
//Declare variables
/*var input, table, filter tr, td, x;
input = document.getElementById("input");
filter = input.value.toString();
table = document.getElementById("table");
tr = document.getElementsByTagName("tr");
//Filter Table
for(x = 0; x < tr.length(); x++){
  td = tr[x].getElementsByTagName("td")[0];
  if(td){
    if(td.innerHTML.toString().indexOf(filter) > -1){
      tr[x].style.display = "";
    }
    else{
      tr[x].style.display = "none";
    }
  }
}
}*/
  //jQuery
  //Bootstrap Popover
	$(function () {
		$('[data-toggle="popover"]').popover()
	})

</script>

</body>
</html>