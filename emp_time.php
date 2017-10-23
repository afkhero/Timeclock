<?php
require_once 'report.php';

# EmployeeTime: A class that encapsulates employee timeclock access functionality
# The EmployeeTime class takes in an instance to a mysql DB and an instance to wheniwork.
# It then supplies the user with the ability to clock in/out given a students rfid_no. And
# it also gives the user to the ability to view the times an employee has worked.

class EmployeeTime
{
	private $BAD_DB = 1;
	private $EMPTY_RES = 2;
	private $MULTI_RES = 3;
	private $BAD_EDIT = 4;
	private $SUCC = 0;

	private $mysqli;
	private $wiw;

	#
	# Setups our new instance of EmployeeTime with access to mysql and wheniwork
	#
	public function __construct($mysql_handle, $wiw_handle){
		$this->mysqli = $mysql_handle;
		$this->wiw = $wiw_handle;
	}

	#
	# @param utaid => the student workere we're looking at
	# @param datestr => the date indicating the pay period we're looking at
	#
	# @return Report Object => a report object holding the date, start time, end time, and duration
	#
	public function view_times($utaid, $datestr){
		
		#find our date range
		#pay periods are from the 1st to the 15th or the 16th to the end of the month
		$date = date_create_from_format("m-j-y", $datestr);
		$timestamp = $date->getTimestamp();
		$begin_d = 1; 
		$end_d = 15;
		#We're in the second pay period
		if(idate('d', $date->getTimestamp()) > 15){
			$begin_d = 15;
			$end_d = idate('t', $date->getTimestamp());
		}

		$begin = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $begin_d);
		$end = date_date_set(date_create(), idate('Y', $timestamp), idate('m', $timestamp), $end_d);

		#our query for finding times between our start and end datetimes
		$sql="SELECT start_time, end_time, duration FROM time_clock WHERE staff_id='".$utaid."' AND NOT end_time is NULL AND start_time>='".$begin->format("Y-m-d")." 00:00:00' AND start_time<='".$end->format("Y-m-d")." 23:59:59'";
		$res = $this->mysqli->query($sql);

		#pull our student workers name from wiw via our utaid to wiwid lookup table
		$wiw_id_res = $this->mysqli->query("SELECT wiw_id FROM wiw WHERE uta_id='".$utaid."'");
		if($wiw_id_res->num_rows == 0){
			return $this->EMPTY_RES;
		}
		if($wiw_id_res->num_rows > 1){
			return $this->MULTI_RES;
		}
		$wiw_id = $wiw_id_res->fetch_assoc();
		if($wiw_id == false){
			return $this->EMPTY_RES;
		}

		$user_obj = $this->wiw->get("users/".$wiw_id['wiw_id']);
		$name = "".$user_obj->user->first_name." ".$user_obj->user->last_name;

		#lets build our report
		$title = "Timesheet: ".$name;
		$report = array();
		if($res->num_rows > 0){
			$report[] = array("date", "start", "end", "duration");
			while($row = $res->fetch_assoc()){
				$start = date_create_from_format("Y-m-d H:i:s", $row['start_time']);
				$end = date_create_from_format("Y-m-d H:i:s", $row['end_time']);
				$item = array();
				$item[] = $start->format("Y-m-d");
				$item[] = $start->format("g:ia");
				$item[] = $end->format("g:ia");
				$item[] = $row['duration'];
				$report[] = $item;
			}
		}
		#notice $report is a multi-dimensional array of string
		#this is so we can organize our Report object in a meaningful way
		return new Report($title, $report);
	}

	#
	#
	#
	public function clock($rfid_no){
		if(!isset($this->mysqli)){
			return $this->BAD_DB;
		}

		$rfid = htmlspecialchars($rfid_no);
		$sql = "SELECT operator FROM rfid WHERE rfid_no='".$rfid."'";
		$result = $this->mysqli->query($sql);

		if($result->num_rows == 0){// couldn't find operator
			return $this->EMPTY_RES;
		}
		else if($result->num_rows > 1){// couldn't resolve operator
			return $this->MULTI_RES;
		}
		else {
			$row = $result->fetch_array();
			$clk_sql = "SELECT staff_id FROM time_clock WHERE staff_id='".$row[0]."' AND end_time is NULL";
			$clk_res = $this->mysqli->query($clk_sql);

			if($clk_res->num_rows > 1){// operator clocked in mult. times
				return $this->MULTI_RES;
			}
			else if($clk_res->num_rows == 0){// operator not clocked in so lets do that
				return $this->clock_in($row['operator']);
			}
			else {// operator is clocked in lets clock em out
				return $this->clock_out( $row['operator']);
			}
		}

	}

	private function clock_in($utaid){
		$sql = "INSERT INTO time_clock ( staff_id, start_time ) VALUES ('$utaid', CURRENT_TIMESTAMP )";
		$res = $this->mysqli->query($sql);
		if($res){
			//call WIW
			return $this->SUCC;
		}
		return $this->BAD_EDIT;
	}

	private function clock_out($utaid){
		$sql = "UPDATE time_clock SET end_time=CURRENT_TIMESTAMP, duration=SEC_TO_TIME(TIMESTAMPDIFF(SECOND, start_time, CURRENT_TIMESTAMP)) WHERE staff_id='$utaid' AND end_time is NULL";
		$res = $this->mysqli->query($sql);
		if($res){
			//call WIW
			return $this->SUCC;
		}
		return $this->BAD_EDIT;
	}
}
?>