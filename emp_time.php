<?php
define("BAD_DB", 1);
define("EMPTY_RES", 2);
define("MULTI_RES", 3);
define("BAD_EDIT", 4);
define("SUCC", 0);

function emp_clk($mysqli, $rfid_no){
	if(!isset($mysqli)){
		return BAD_DB;
	}

	$rfid = htmlspecialchars($rfid_no);
	$sql = "SELECT operator FROM rfid WHERE rfid_no='".$rfid."'";
	$result = $mysqli->query($sql);

	if($result->num_rows == 0){// couldn't find operator
		return EMPTY_RES;
	}
	else if($result->num_rows > 1){// couldn't resolve operator
		return MULTI_RES;
	}
	else {
		$row = $result->fetch_array();
		$clk_sql = "SELECT staff_id FROM time_clock WHERE staff_id='".$row[0]."' AND end_time is NULL";
		$clk_res = $mysqli->query($clk_sql);

		if($clk_res->num_rows > 1){// operator clocked in mult. times
			return MULTI_RES;
		}
		else if($clk_res->num_rows == 0){// operator not clocked in so lets do that
			return emp_clk_in($mysqli, $row['operator']);
		}
		else {// operator is clocked in lets clock em out
			return emp_clk_out($mysqli, $row['operator']);
		}
	}

}

function emp_clk_in($mysqli, $utaid){
	$sql = "INSERT INTO time_clock ( staff_id, start_time ) VALUES ('$utaid', CURRENT_TIMESTAMP )";
	$res = $mysqli->query($sql);
	if($res){
		//call WIW
		return SUCC;
	}
	return BAD_EDIT;
}

function emp_clk_out($mysqli, $utaid){
	$sql = "UPDATE time_clock SET end_time=CURRENT_TIMESTAMP, duration=SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, CURRENT_TIMESTAMP)) WHERE staff_id='$utaid' AND end_time is NULL";
	$res = $mysqli->query($sql);
	if($res){
		//call WIW
		return SUCC;
	}
	return BAD_EDIT;
}

function emp_view_times($mysqli, $utaid, $datestr){
	$date = date_create_from_format("m-j-y", $datestr);
	$timestamp = $date->getTimestamp();
	$begin_d = 1; 
	$end_d = 15;

	if(idate('d', $date->getTimestamp()) > 15){
		$begin_d = 15;
		$end_d = idate('t', $date->getTimestamp());
	}

	$begin = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $begin_d);
	$end = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $end_d);

	$sql="SELECT start_time, duration FROM time_clock WHERE staff_id='".$utaid."' AND NOT end_time is NULL AND start_time>='".$begin->format("Y-m-d")." 00:00:00' AND start_time<='".$end->format("Y-m-d")." 23:59:59'";
	$res = $mysqli->query($sql);

	if($res->num_rows > 0){//consider changing to return a printer friendly string
		echo "$utaid TimeSheet<br><table><tr><th>Date</th><th>Hours</th></tr>";
		while($row = $res->fetch_assoc()){
			echo "<tr><td>".$row['start_time']."</td><td>".$row['duration']."</td></tr>";
		}
		echo "</table>";
	}
}

?>
