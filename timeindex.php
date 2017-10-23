<?php 
	require_once ($_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php");
	require_once 'wiw_connect.php';
	include 'report.php';
	include 'emp_time.php'; 
	//include 'admin_time.php';

	$emp_time = new EmployeeTime($mysqli, $wiw);
?>

<!DOCTYPE html>
<html lang="en">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />
<link rel="stylesheet" href="style.css" type="text/css" />

<style>
table {border-collapse: collapse; width: 50%;}
table, th, td{border: 1px solid black; height: 25px; text-align: center;}
table, p{font-size: 17px;}
</style>

<head><title>FA - TimeClock</title></head>

<body>
<!-- Role Selection -->
	<p>
		<form action="" method="GET">
			<input type="submit" value="Employee" name="role">
			<input type="submit" value="Admin" name="role">
		</form>
	</p>
<!-- Dashboard -->
	<p>Role: 
<?php 
	if(isset ($_GET['role'])){
		if($_GET['role'] == "Employee"){	
?>

			Employee </p>
			<p>
				<form action="" method="POST">
					<input type="text" placeholder="rfid#" value="" name="rfid">
						<input type="submit" value="Clock" name="emp_act"><br>
					<input type="date" placeholder="<?php echo date('m-d-y');?>" value="" name="emp_v_t"><br>
						<input type="text" placeholder="1000#" value="" name="emp_id">
						<input type="submit" value="View Times" name="emp_act"><br>
				</form>
			</p>

<?php
	 	}
		elseif($_GET['role'] == "Admin"){	
			echo "Administration<br><bold><h1>Coming Soon!</h1></bold><br>";
	 	}
	}
	else{
		echo "Choose<br>";
	}

#!-- Carry out Action --
	if(isset($_POST['emp_act'])){
		if($_POST['rfid'] != ""){$emp_time->clock($_POST['rfid']);}
		if($_POST['emp_v_t'] != "" && $_POST['emp_id'] != ""){
			$times = $emp_time->view_times($_POST['emp_id'], $_POST['emp_v_t']);
			echo $times->html();
			echo "<br>";
		}
	}
	elseif (isset($_POST['admin_act'])){
		
	}
?>
<!-- View DataBase -->
<h1>Time Clock</h1>
<table>
	<tr>
		<th>staff_id</th>
		<th>start_time</th>
		<th>end_time</th>
		<th>duration</th>
	</tr>
<?php
	$result = $mysqli->query("SELECT staff_id,start_time,end_time,duration FROM time_clock");

	while($row = $result->fetch_assoc()){
		echo "<tr>";

		$keys = array_keys($row);
		foreach($keys as $key){
			echo "<td>".$row[$key]."</td>";
		}

		echo "</tr>";
	}
?>
</table>




<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" >
 
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" >
 
<link rel="stylesheet" href="style.css" >

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</body>

</html>