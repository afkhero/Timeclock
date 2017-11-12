<?php
	#require_once ($_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php");
	require_once ("/home/michael/fabapp/connections/db_connect8.php");
	require_once 'wiw_connect.php';
	
	pull_shifts($mysqli, $wiw);
	
	function pull_shifts($mysqli, $wiw){
		$y_morn = new DateTime("yesterday");
		$y_morn->setTime(0, 0);
		$y_night = new DateTime("yesterday");
		$y_night->setTime(23, 59, 59);

		$shifts_obj = $wiw->get("shifts", array("start" => $y_morn->format("r"), 
			                                    "end" => $y_night->format("r"), 
			                                    "unpublished" => false, "deleted" => false));

		$ins_qry = $mysqli->prepare(
			"INSERT INTO past_shift (shift_id, user_id, start_time, end_time) VALUES (?, ?, ?, ?);"
		);
		$ins_qry->bind_param('ssss', $id, $user, $start, $end);

		foreach ($shifts_obj->shifts as $shift){
			$id = $shift->id;
			$user = $shift->user_id;
			$start = date_format(new DateTime($shift->start_time), "Y-m-d H:i:s");
			$end = date_format(new DateTime($shift->end_time), "Y-m-d H:i:s");	

			$ins_qry->execute();
		}
	}

?>