<?php
require_once ($_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php");

echo $_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php<br>";

emp_clk_in("54321");

function emp_clk_in($rfid_no){
	if(!isset($mysqli)){
		echo "No DB connection<br>";
		return;
	}
	$rfid = htmlspecialchars($rfid_no);
	$sql = "SELECT operator FROM rfid WHERE rfid_no='$rfid'";
	$result = mysqli_query($mysqli, $sql);
	echo $sql."<br>";
	$count = mysqli_num_rows($result);
	echo $count."<br>";
	if($count == 0){
		echo "Operator not found<br>";
	}
	else if($count > 1){
		echo "Operator could not be resolved<br>";
	}
	else { //you may clock in
		$row = mysqli_fetch_assoc($result);

		$ins_res = mysqli_query(mysqli, 'INSERT INTO `time_clock` ( staff_id, start_time ) VALUES ( '.$row['operator'].', CURRENT_TIMESTAMP )');

		if($ins_res){
			//go to wiw 
			echo "<h1>Work Bitches</h1>";
		} else {
			//display an error msg
			echo "<i>guess I'm not working</i>";
		}
	}
}

function emp_clk_out($rfid_no){

}

function emp_view_times($utaid, $date){

}

?>
