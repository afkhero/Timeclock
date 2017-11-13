<?php
require_once 'report.php';
require_once 'result.php';

class AdminTime{
	
	private $mysqli;
	private $wiw;

	function __construct($mysqli_handle, $wiw_handle){
		$this->mysqli = $mysqli_handle;
		$this->wiw = $wiw_handle;
	}

	function edit_time($userID, $shiftID, $startTime, $endTime){
        
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

	function list_staff(){
		$today = new DateTime();
		$timestamp = $today->getTimestamp();

		switch($today->format("D")){
			case "Mon": $bwp = false; $ewp = "P6D";
				break;
			case "Tue": $bwp = "P1D"; $ewp = "P5D";
				break;
			case "Wed": $bwp = "P2D"; $ewp = "P4D";
				break;
			case "Thur": $bwp = "P3D"; $ewp = "P3D";
				break;
			case "Fri": $bwp = "P4D"; $ewp = "P2D";
				break;
			case "Sat": $bwp = "P5D"; $ewp = "P1D";
				break;
			case "Sun": $bwp = "P6D"; $ewp = false;
				break;
			default: return false;
		}

		$begin_w = new DateTime();
		$end_w = new DateTime();

		if($bwp){
			$begin_w->sub(new DateInterval($bwp));
		}

		if($ewp){
			$end_w->add(new DateInterval($ewp));			
		}

		$begin_w->setTime(0, 0, 0);
		$end_w->setTime(23, 59, 59);

		$bppd = 1;
		$eppd = 15;

		if(idate('d', $timestamp) > 15){
			$bppd = 16;
			$eppd = idate('t', $timestamp);
		}

		$timestamp = $today->getTimestamp();
		$begin_pp = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $bppd);
		$end_pp = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $eppd);

		$begin_pp->setTime(0, 0, 0);
		$end_pp->setTime(23, 59, 59);

/******************************************************
		This is the query to get a student's:
			1) wheniwork id
			2) uta 1000#
			3) hours they worked in the current week
			   (NULL if they haven't worked)
			4) hours they have worked in the current pay period
			   (NULL again if they haven't worked)

		SELECT wiw_id, uta_id, hr_week, hr_payp
		FROM (
			SELECT wiw_id, uta_id
			FROM wiw
		) people
		LEFT JOIN (
			SELECT weekly.staff_id AS staffid, hr_week, hr_payp
			FROM (
				SELECT staff_id, SEC_TO_TIME(SUM(TIME_TO_SEC(duration))) as hr_week
				FROM time_clock 
				WHERE start_time BETWEEN '?' AND '?'
				GROUP BY staff_id
			) weekly
			JOIN (
				SELECT staff_id, SEC_TO_TIME(SUM(TIME_TO_SEC(duration))) as hr_payp
				FROM time_clock 
				WHERE start_time BETWEEN '?' AND '?'
				GROUP BY staff_id
			) payper
			ON weekly.staff_id=payper.staff_id
		) hours
		ON uta_id=staffid;
******************************************************/

		$query = $this->mysqli->prepare("SELECT wiw_id, uta_id, hr_week, hr_payp FROM (SELECT wiw_id, uta_id FROM wiw) people LEFT JOIN (SELECT weekly.staff_id AS staffid, hr_week, hr_payp FROM (SELECT staff_id, SEC_TO_TIME(SUM(TIME_TO_SEC(duration))) as hr_week FROM time_clock WHERE start_time BETWEEN ? AND ? GROUP BY staff_id) weekly JOIN (SELECT staff_id, SEC_TO_TIME(SUM(TIME_TO_SEC(duration))) as hr_payp FROM time_clock WHERE start_time BETWEEN ? AND ? GROUP BY staff_id) payper ON weekly.staff_id=payper.staff_id ) hours ON uta_id=staffid;");

		$bw_str = $begin_w->format('Y-m-d H:i:s');
		$ew_str = $end_w->format('Y-m-d H:i:s');
		$bpp_str = $begin_pp->format('Y-m-d H:i:s');
		$epp_str = $end_pp->format('Y-m-d H:i:s');

		$query->bind_param('ssss', $bw_str, $ew_str, $bpp_str, $epp_str);
		
		$query->bind_result($wiw_id, $uta_id, $hr_week, $hr_payp);

		$list = array();
		$list[] = ['Name', 'UTA ID', 'Week', 'Pay Period'];
		while($query->fetch()){

			$item = array();
			$user_obj = $this->wiw->get("users/".$wiw_id);
			$name = "".$user_obj->user->first_name." ".$user_obj->user->last_name;

			$item[] = $name;
			$item[] = $uta_id;

			if($hr_week == NULL){
				$item[] = "00:00:00";
			}else{
				$item[] = $hr_week;
			}

			if($hr_payp == NULL){
				$item[] = "00:00:00";
			}else{
				$item[] = $hr_payp;
			}

			$list[] = $item;
		}

		return new Report("Employees",$list);
	}
}
?>
