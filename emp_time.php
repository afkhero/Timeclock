<?php
define("BAD_DB", 1);
define("EMPTY_RES", 2);
define("MULTI_RES", 3);
define("UNIQUE_RES", 4);
define("BAD_EDIT", 5);
define("SUCC", 0);

function emp_clk_in($mysqli, $rfid_no){
	if(!isset($mysqli)){
		return BAD_DB;
	}

	$rfid = htmlspecialchars($rfid_no);
	$sql = "SELECT operator FROM rfid WHERE rfid_no='".$rfid."'";
	$result = $mysqli->query($sql);

	$count = $result->num_rows;
	if($count == 0){
		return EMPTY_RES;
	}
	else if($count > 1){
		return MULTI_RES;
	}
	else { //you may clock in
		$row = $result->fetch_assoc();
		$in_sql = "INSERT INTO time_clock ( staff_id, start_time ) VALUES ('".$row['operator']."', CURRENT_TIMESTAMP )";
		$in_res = $mysqli->query($in_sql);

		if($in_res){
			//go to wiw

			return SUCC;
		} else {
			//display an error msg
			return BAD_EDIT;
		}
	}
}

function emp_clk_out($mysqli, $rfid_no){
	
}

function emp_view_times($mysqli, $utaid, $date){

}

?>
