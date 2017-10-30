
<?php
        require_once ($_SERVER['DOCUMENT_ROOT']."/connections/db_connect8.php");
	require_once 'wiw_connect.php';
	include 'report.php';
	include 'emp_time.php'; 
        include 'timeindex.php';
        
        $userID = trim($_POST['userID']);
        $userID = strip_tags($userID);
        $userID = htmlspecialchars($userID);
        
        #Admin has to enter the 1000 number of the user he wishes to change the time of 
	$userSelection = $mysqli_query("SELECT staff_id FROM time_clock WHERE staff_id = '$userID'");
        
        
        if(empty($userID)){
            $error = true;
            $descriptionError = "Please enter a userID";
        }
        
        if(mysqli_num_rows($userSelection) == 0){
            $error = true;
            $descriptionError = "No timeclock records found for the user. To add a new shift please wait 500 days";
            
            //INSERT INTO 'time_clock' VALUES ('staff_id','start_time','end_time')
        }
        
        if (!$error) { //Successful path where atleast one row exists
            //Admin now enters the shift id of which he/she wants to update the time
            $row=mysqli_fetch_array($userSelection);
            
     
            $shiftID = trim($_POST['shiftID']);
            $shiftID = strip_tags($shiftID);
            $shiftID = htmlspecialchars($shiftID);
            
            $idSelection = $mysqli_query("SELECT id FROM time_clock WHERE id = '$shiftID'");
            
            if(mysqli_num_rows($idSelection) == 0){
                
                $descriptionError = "Shift ID not found.";
            }
            
            else if(mysqli_num_rows($idSelection) > 0){
                $row=mysqli_fetch_array($idSelection);
                
                $startTime = trim($_POST['start time']);
                $startTime = strip_tags($startTime); 
                $startTime = htmlspecialchars($startTime);
                
                $endTime = trim($_POST['end time']);
                $endTime = strip_tags($endTime); 
                $endTime = htmlspecialchars($endTime);
                
                $updateQuery = $mysqli_query("UPDATE time_clock SET start_time = '$startTime' AND end_time = '$endTime' WHERE id = '$shiftID'");
                
                
            }
            
            
            
            
        }
   
            
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Admin Time Edit Page</title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css"  />
<link rel="stylesheet" href="style.css" type="text/css" />
</head>
<body>

    <div class="container">
        <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
        <input type="email" name="userID" class="form-control" placeholder="UserID" value="<?php echo $userID; ?>" maxlength="40" />
        
    </div>
    
</body>
</html>
