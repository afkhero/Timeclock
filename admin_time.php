<!DOCTYPE html>
<html>

<?php
//require_once 'report.php';
require_once 'result.php';

class AdminTime{
	
	private $mysqli;
	private $wiw;

	function __construct($mysqli_handle, $wiw_handle){
		$this->mysqli = $mysqli_handle;
		$this->wiw = $wiw_handle;
	}

	function edit_time($userID, $shiftID, $startTime, $endTime){
        #$userID = strip_tags($userID);
        #$userID = htmlspecialchars($userID);
        
        #Admin has to enter the 1000 number of the user he wishes to change the time of ""
		$userSelection = mysqli_query($this->mysqli, "SELECT staff_id FROM time_clock WHERE staff_id='".$userID."';");
        
        $error = false;
        if(empty($userID)){
            $error = true;
            $descriptionError = "Please enter a userID";
        }
        
        if(mysqli_num_rows($userSelection) == 0){
            $error = true;
            $descriptionError = "No timeclock records found for the user. To add a new shift please wait 500 days";
            
            //INSERT INTO 'time_clock' VALUES ('staff_id','start_time','end_time', duration=SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, end_time)))
        }
        
        if (!$error) { //Successful path where atleast one row exists
            //Admin now enters the shift id of which he/she wants to update the time
            $row=mysqli_fetch_array($userSelection);
                 
            $shiftID = strip_tags($shiftID);
            $shiftID = htmlspecialchars($shiftID);
            
            $idSelection = mysqli_query($this->mysqli, "SELECT id FROM time_clock WHERE id = '$shiftID'");
            
            if(mysqli_num_rows($idSelection) == 0){
                
                $descriptionError = "Shift ID not found.";
            }
            
            else if(mysqli_num_rows($idSelection) > 0){
                $row=mysqli_fetch_array($idSelection);
                
                $start_time = $startTime->format("Y-m-d H:i:00");
                $end_time = $endTime->format("Y-m-d H:i:00");
                
                $updateQuery = mysqli_query($this->mysqli, "UPDATE time_clock SET start_time='$start_time', end_time='$end_time',duration=SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, end_time)) WHERE id=$shiftID");
            }
        }
	}
        
        function new_time($staffID, $startTime, $endTime)
        {
            $staffID = strip_tags($staffID);
            $staffID = htmlspecialchars($staffID);
            
            $start_time = $startTime->format("Y-m-d H:i");
            $end_time = $endTime->format("Y-m-d H:i");
            
            $insertQuery = mysqli_query($this->mysqli, "INSERT INTO `time_clock` (`id`, `staff_id`, `start_time`, `end_time`, `duration`) VALUES (NULL,`$staffID`,`$start_time`,`$end_time`,duration=SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, end_time)))");
            
        
            
            
        }
        
	function list_staff($startday, $endday){

	$wiw_id_res = $this->mysqli->query("SELECT wiw_id, uta_id, SEC_TO_TIME( SUM(TIME_TO_SEC(duration))) AS h_worked
				FROM time_clock t join wiw w on t.staff_id = w.uta_id
				WHERE start_time BETWEEN date('$startday') AND date('$endday')
				group by uta_id" );

	$list = array();
	while($row = $wiw_id_res->fetch_assoc()){
		$item = array();

		$user_obj = $this->wiw->get("users/".$row['wiw_id']);
		$name = "".$user_obj->user->first_name." ".$user_obj->user->last_name;
		$item[] = $name;
		$item[] = $row['wiw_id'];
		$item[] = $row['uta_id'];
		$item[] = $row['h_worked'];
		$list[] = $item;
	}
?>

<table>
		<tr>
			<th>Name</th>
			<th>Wiw_ID</th>
			<th>UTA_ID</th>
			<th>H-Worked</th>
		</tr>
<?php

	foreach ($list as $value) {
		echo "<tr>";
		echo "<td>".$value['0']."</td>";
		echo "<td>".$value['1']."</td>";
		echo "<td>".$value['2']."</td>";
		echo "<td>".$value['3']."</td>";
		echo "</tr>";
	}
?>
</table>

<?php

	}
}
?>


</html>