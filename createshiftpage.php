<?php
function getslottimes() {
	$times = array();
//db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
//pass SQL errors to PHP
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//create db connection
	$conn = new mysqli($servername, $username, $password, $dbname);
//check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
//open connection to db
	$conn = mysqli_connect($servername, $username, $password, $dbname);
//set query text to select the current day slots' start and end times
	$query = "select slotstarttime, slotendtime from FPGC where slotdate = '" . date('Y-m-d') . "' group by slotstarttime order by slotstarttime DESC";
	//$query = "select slotstarttime, slotendtime from FPGC where slotdate = '2024-08-14' group by slotstarttime order by slotstarttime DESC";
//run query
	$result = $conn->query($query);
	echo $result->num_rows;
	if ($result->num_rows > 0) {
		while ($row = $result -> fetch_row()) {
		array_push($times,$row);
		}
	} 
	else {
		echo "0 results";
		return null;
	}
//sort slot times	
	usort($times, function ($a, $b) {
		return DateTime::createFromFormat('h:i A', $a[0]) <=> DateTime::createFromFormat('h:i A', $b[0]);
	});
	return $times;
}

function getvolunteers() {
	$volunteers = array();
//db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
//pass SQL errors to PHP
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//create db connection
	$conn = new mysqli($servername, $username, $password, $dbname);
//check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
//open connection to db
	$conn = mysqli_connect($servername, $username, $password, $dbname);
//set query text to select all of the days volunteers and start times
	$query = "select slotstarttime, slotfirst, slotlast from FPGC where slotdate = '" . date('Y-m-d') . "' order by slotstarttime DESC";
	//$query = "select slotstarttime, slotfirst, slotlast from FPGC where slotdate = '2024-08-14' order by slotstarttime DESC";
	
//run query
	$result = $conn->query($query);
	if ($result->num_rows > 0) {
		while ($row = $result -> fetch_row()) {
		array_push($volunteers,$row);
		}
	} 
	else {
		echo "0 results";
		return null;
	}
	
	return $volunteers;
}

function getcouriers() {
	$volunteers = array();
//db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
//pass SQL errors to PHP
	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
//create db connection
	$conn = new mysqli($servername, $username, $password, $dbname);
//check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
//open connection to db
	$conn = mysqli_connect($servername, $username, $password, $dbname);
//set query text to select all of the days volunteers and start times
	$query = "select slotdate, slotfirst, slotlast, slotlocation from FPPU where slotdate = '" . date('Y-m-d') . "' order by slotlocation DESC";
	//$query = "select slotdate, slotfirst, slotlast, slotlocation from FPPU where slotdate = '2024-08-14' order by slotlocation DESC";
	
//run query
	$result = $conn->query($query);
	if ($result->num_rows > 0) {
		while ($row = $result -> fetch_row()) {
		array_push($volunteers,$row);
		}
	} 
	else {
		echo "0 results";
		return null;
	}
	
	return $volunteers;
}
//get today's slot times
	$slottimes = getslottimes();
//get today's FP volunteers
	$slotvolunteers = getvolunteers();
//get today's couriers
	$couriers = getcouriers();
//set filepath for HTML output file
	$filepath = "/var/www/html/dailyshift.html";
//start HTML code
	$message = "
	<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">

	<head>
		<title>Food Pantry Daily Shift Page</title>
		<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\" />
		<meta name=\"generator\" content=\"Geany 1.38\" />
	</head>

	<body>";
	//look for FP closed	
	if($slotvolunteers == null) {
		$message = $message . "<p style=\"font-size:60px; font-family: Arial, Helvetica, sans-serif; line-height: 20px; height: 20px; text-align: center;\"><u>We Are Closed Today</u></p>
		</body>
		</html>";
		$myfile = fopen($filepath, "w") or die("Unable to open file!");
		fwrite($myfile, $message);
		fclose($myfile);
	}else{
	$message = $message . "<p style=\"font-size:60px; font-family: Arial, Helvetica, sans-serif; line-height: 20px; height: 20px; text-align: center; color: #1f69b1;\"><b><u>Today's Pantry Volunteers</u></b></p>";

//if volunteer data is present, proceed		
	$message = $message . "<font size=\"6\" face=\"Arial\" >
	<table style=\"margin-left: auto; margin-right: auto;\" border=1 cellpadding=20px>
		<h2 align=center>
		<p class=\"normal\">
		<tr>";
//Add slot times to FP table column headers
	foreach($slottimes as $stimes) {
		$message = $message . 
		"<th align=center style=\"padding-top: 2px; padding-bottom: 2px;  background-color: #c5daf1; \"><b>" . $stimes[0] . "-" . $stimes[1] . "</b>";		
	}
	$message = $message . "</th><tr align=center>";
//add volunteer names to slot time columns
	foreach($slottimes as $stimes) {
		$message = $message . "<td style=\"padding-top: 10px; padding-bottom: 10px; color: #1f69b1;\"><b>";
		foreach($slotvolunteers as $vols) {
			if($vols[0] == $stimes[0]){
				$vname = $vols[1] . " " . substr($vols[2],0,1) . ".";
				if(substr($vname,0,1) != "*"){
					$message = $message . $vname . "<br>";
				}
			}
		}
		$message = $message . "</b></td>";
	}
	$message = $message . 
	"</tr>
	</table>
	<p style=\"font-size:60px; font-family: Arial, Helvetica, sans-serif; line-height: 20px; height: 20px; text-align: center; color:#178a51;\"><b><u>Delivery Couriers</u></b></p>";
	$message = $message . "
	<table style=\"margin-left: auto; margin-right: auto;\" border=1 cellpadding=20px>
		<h2 align=center>
		<p class=\"normal\">
		<tr>";
//add locations to table clomn headers		
	foreach($couriers as $cours) {
		$message = $message . 
		"<th align=center style=\"padding-top: 2px; padding-bottom: 2px; background-color: #55da97;\"><b>" . $cours[3] . "</b>";		
	}
	$message = $message . "</th><tr align=center>";
//add volunteer names to slot time columns
	foreach($couriers as $cours) {
		$message = $message . "<td style=\"padding-top: 10px; padding-bottom: 10px; color:#178a51;\"><b>";
		if(substr($cours[1],0,1) == "*"){
			$vname = "???";
			$message = $message . $vname;
		}else{
			$vname = $cours[1] . " " . substr($cours[2],0,1) . ".";
			$message = $message . $vname;
			}
		$message = $message . "</b></td>";
		}
	$message = $message . "</tr></td></table>";
	
	$message = $message . "
	<p style=\"font-size:60px; font-family: Arial, Helvetica, sans-serif; line-height: 20px; height: 20px; text-align: center; color:#714db7;\"><b><u>Staff</u></b></p>
	</font>
	</body>
	</html>
	";
	$myfile = fopen($filepath, "w") or die("Unable to open file!");
	fwrite($myfile, $message);
	fclose($myfile);
}
?>

