<?php
//include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//include PHPMailer code files
require '/var/www/html/PHPMailer/Exception.php';
require '/var/www/html/PHPMailer/PHPMailer.php';
require '/var/www/html/PHPMailer/SMTP.php';

//get start date (Today's Date)
function getstartdate(){
	$StartDate = date('Y-m-d');
	return $StartDate;
}

//get end business date based on department and signup type
function getenddate($dept,$stype){
	$dates = array();
	$date = new DateTime();
	$tz = new DateTimeZone('America/New_York');
	$date->setTimezone($tz);
//if FP and PU, include Mon-Sat	
	if($dept == 'FP' && $stype == 'PU'){
		$dates[] = $date->format('Y-m-d');
		while (count($dates)<5)
		{
			$date->add(new DateInterval('P1D'));
			if ($date->format('N')<7)
				$dates[]=$date->format('Y-m-d');
		}
		return $dates[4];
	}
//if CC and PU, include all days	
	if($dept == 'CC' && $stype == 'PU'){
		$dates[] = $date->format('Y-m-d');
		while (count($dates)<5)
		{
			$date->add(new DateInterval('P1D'));
			$dates[]=$date->format('Y-m-d');
		}
		return $dates[4];
//include Mon-Fri for in-house slots			
	} else {
		$dates[] = $date->format('Y-m-d');
		while (count($dates)<5)
		{
			$date->add(new DateInterval('P1D'));
			if ($date->format('N')<6)
				$dates[]=$date->format('Y-m-d');
		}
		return $dates[4];
	}

}

//pull data from SUG for daily email process
function pulldata($sid){
//setup curl connection to SUG API		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.signupgenius.com/v2/k/signups/report/available/" . $sid . "/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	$headers = array();
	$headers[] = "Accept: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//run curl and check for errors
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		$curlerror = 'Error:' . curl_error($ch);
	} else {
		$curlerror = 'OK';
	}
	curl_close ($ch);
//decode json data from SUG APi and load data into $signup	
	$obj = json_decode($result, false);
	$data = $obj->data;
	$signup = $data->signup;
	$myArray = array();
//loop through signup slots and format data for mysignup class objects
	foreach ($signup as $mysignup) {
//set time zone to EST		
		$offset = $mysignup->offset;
		$offset1 = substr($offset, 4, -3);
//format slot start date
		$startdate = strtotime($mysignup->startdatestring);
		$startdatearray = getDate($startdate);
		$startdate1 = date('l, m/d/Y', $startdate);
//format slot start time		
		$starttime = $startdatearray['hours'];
		$starttime1 = $starttime - $offset1;
		$starttime2 = $starttime1 . ":" .$startdatearray['minutes'];
		$time = date_create($starttime2);
		$starttime3 = date_format($time, "h:ia");
//get slot weekday
		$weekday = $startdatearray['weekday'];
//format slot end date
		$enddate = strtotime($mysignup->enddatestring);
		$enddatearray = getdate($enddate);
//format slot end time
		$endtime = $enddatearray['hours'];
		$endtime1 = $endtime - $offset1;
		$endtime2 = $endtime1 . ":00";
		$time1 = date_create($endtime2 );
		$endtime3 = date_format($time1, "h:ia");
		$slotid = $mysignup->slotitemid;
//create new MySignUpClass object and intialize with formatted slot data
		$obj = new MySignUpClass($weekday, $mysignup->startdatestring,$startdate1,$starttime3,$endtime3,$mysignup->myqty,$mysignup->item,$slotid);
//put new MySignUpClass object into array
		array_push($myArray, $obj);
	}
//return arrays of raw signup data, MySignUpClass objects, and curl error(if happens)
	//return array($signup,$myArray,$curlerror);
	return array($myArray,$curlerror);
}

//sort MySignUpClass object in array by ascending date and time
function sortdates($myArray,$formStartDate,$formEndDate,$vtype){
//sort MySignUpClass objects by date
	usort($myArray, [MySignUpClass::class, "cmp_obj"]);
//initialize an empty result arrays
	$sortedData = [];
	$filteredData = [];
//loop through sorted MySignUpClass objects and push ones in date range to array
	foreach ($myArray as $arrSort) {
		$abc = strtotime($arrSort->mystartdate);
		$abc = date('Y-m-d', $abc);
		if (!empty($formStartDate) && !empty($formEndDate)) {
			if ($abc >= $formStartDate && $abc <= $formEndDate) {
			$filteredData[] = $arrSort;
			}
		}
	}
//return sorted array of MySignUpClass objects that fall within date range
	return $filteredData;
}

//Format email message containing slot data in HTML
function createmessage($filteredData,$formStartDate,$formEndDate,$signupname,$vtype){

	if (!empty($formStartDate) && !empty($formEndDate) && !empty($filteredData)) {
		$message = "<table border=1 cellpadding=5px>
		<h2 align=center>
		<p class=\"normal\">";
//change main title text depending on signup type
		if($vtype == 'PU'){
			$message = $message . "The " . $signupname . " Signup <br> Missing Pickups Next Five Days";
		} else {
			$message = $message . "The " . $signupname . " Signup <br> Missing Shifts Next Five Days";
		}
		$message = $message . "</p>
		</h2>
		<tr>
		<th align=center><b>
		Date
		</b></th>";
//format table for PU or GC data headers
		if($vtype == 'PU'){
			$message = $message . "
			<th align=center><b>
			Location
			</b></th>
			</tr>";
		} else {
		$message = $message . "<th align=center><b>
		Time Slot
		</b></th>
		<th align=center><b>
		Open Slots
		</b></th>
		</tr>";
		}
//loop through shift data
		foreach ($filteredData as $myfiltered) {
			$message = $message . "<tr>
			<td align=center><b> "
			. $myfiltered->mystartdate;
//format row for PU or GC signup type
			if($vtype == 'PU'){
				$message = $message . "
				<td align=center><b> "
				. $myfiltered->myitem . " </b></td>
					</tr>";
			} else {
				$message = $message . "<td align=center><b> "
				. $myfiltered->mystarttime . " - " . $myfiltered->myendtime . "
				</b></th>";
				if($myfiltered->myopenslots > 1){
					$message = $message . "<td align=center bgcolor='f0b7b7'><b> "
					. $myfiltered->myopenslots . "</b></td></tr>";
				} else {
					$message = $message . "<td align=center><b> "
					. $myfiltered->myopenslots . "
					</b></td>
					</tr>";
				}
			}
		} 
	$message = $message . "</table>";
//return HTML message 
	return $message;
	}
	
//message if there are no empty slots on particular date
	if (!empty($formStartDate) && !empty($formEndDate) && empty($filteredData)) {
		if ($formStartDate == $formEndDate) {
				Print "<p class=\"normal\">";
				Print "No open slots on " . $formStartDate . ".";
				Print "</p>";
		}
//message if there are no empty slots on any date
		else {
			$message = "<h2 align=center>
			<p class=\"normal\">";
			if($vtype == 'PU'){
			$message = $message . "The " . $signupname . " Signup <br> Missing Pickups Next Five Days";
			} else {
				$message = $message . "The " . $signupname . " Signup <br> Missing Shifts Next Five Days";
			}
			$message = $message . "</h2></p>
			<p class=\"normal\">
			No open slots during " . $formStartDate . " and " . $formEndDate . ".
			</p>";
			return $message;
		}
	}
}

//send next 5 days of slot into via email
function emailinfo($message,$emaillist,$depart,$sutype){
//initialize PHPMailer object
	$mail = new PHPMailer(true);

//Server settings
    $mail->SMTPDebug = 2;                      				//Enable verbose debug output
    $mail->isSMTP();                                        //Send using SMTP
    $mail->Host       = 'mail.sallerson.com';               //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                               //Enable SMTP authentication
    $mail->Username   = 'gcvolunteerdata@sallerson.com';    //SMTP username
    $mail->Password   = 'UpInTheG00ch?';                    //SMTP password   
    $mail->SMTPSecure = 'tls';								//Enable implicit TLS encryption
    $mail->Port       = 587;      
    $mail->setFrom('gcvolunteerdata@sallerson.com', 'Signup Auto Mailer');
    $mail->addReplyTo('gcvolunteerdata@sallerson.com', 'Information');
//add email recipients by looping through array    
    foreach($emaillist as $element){
		$mail->addAddress($element);     //Add a recipient
	}
//create subject based on dept and signup type
	if($depart == 'FP'){
		if($sutype == 'GC'){
			$subject = 'Food Pantry Missing Shifts';
		} else {
			$subject = 'Food Pantry Missing Pickups';
		}
	} Else {
		if($sutype == 'GC'){
			$subject = 'Clothes Closet Missing Shifts';
		} else {
			$subject = 'Clothes Closet Missing Pickups';
		}
	}
    $mail->Subject = $subject;
//set message body
    $mail->msgHTML($message);
//send email
    if (!$mail->send()) {
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	} else {
    echo 'Message sent!';
		}
		echo date('Y-m-d', strtotime(' +1 day'));
}

//get signup id number from db
function getsignupid($cur,$program,$vtype){
//set db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
//create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
//check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
//set query text
	$sql = "SELECT signup_id, signup_name FROM signups where current = 1 and program = '" . $program . "' and vol_type = '" . $vtype . "'";
//run query
	$result = $conn->query($sql);
//get signup id from returned rows
	if ($result->num_rows > 0) {
		$row = $result->fetch_row();
	} 
	else {
		echo "0 results";
	}
//close db connection
	$conn->close();
//return signup id
	return ($row);
}

//get list of email addresses from db based on dept type
function getemailaddresses($dept){
//set db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
	
	$emailadds = array();

//create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
//check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
//set query text
	$sql = "SELECT email_address FROM emails where email_dept = '" . $dept . "' or email_dept = 'VC'";
//run query
	$result = $conn->query($sql);
//loop through addresses and put into array
	if ($result->num_rows > 0) {
		while ($row = $result -> fetch_row()) {
		array_push($emailadds,$row[0]);
		}
	} 
	else {
		echo "0 results";
	}
//close db connection
	$conn->close();
//return array of email addresses
	return ($emailadds);
}

//pulld data from SUG API for in-house signups
function pullinhousedata($startdate,$sid){
//set current date	
	$formStartDate = $startdate;
//setup curl connection to SUG API for all slot data			
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.signupgenius.com/v2/k/signups/report/filled/" . $sid . "/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	$headers = array();
	$headers[] = "Accept: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//run curl and check for errors
	$result = curl_exec($ch);
	if (curl_errno($ch)) {
		$curlerror = 'Error:' . curl_error($ch);
	} else {
		$curlerror = 'OK';
	}
//close curl connection
	curl_close ($ch);	
	
//load json signup data into $signup array	
	$obj = json_decode($result, false);
	$data = $obj->data;
	$signup = $data->signup;
	$myArray = array();
//loop through signup slot data and put into MySignUpDataClass objects
	foreach ($signup as $mysignup) {
//set timezone to EST
		$offset = $mysignup->offset;
		$offset1 = substr($offset, 4, -3);
//format start date
		$startdate = strtotime($mysignup->startdatestring);
		$startdatearray = getDate($startdate);
		$startdate1 = date('m/d/Y', $startdate);
//format start time
		$starttime = $startdatearray['hours'];
		$starttime1 = $starttime - $offset1;
		$starttime2 = $starttime1 . ":" . $startdatearray['minutes'];
		$time = date_create($starttime2);
		$starttime3 = date_format($time, "g:i A");
//get weekday
		$weekday = $startdatearray['weekday'];
//get name and slot id #
		$firstname = $mysignup->firstname;
		$lastname = $mysignup->lastname;
		$slotid = $mysignup->slotitemid;
//format end date
		$enddate = strtotime($mysignup->enddatestring);
		$enddatearray = getdate($enddate);
//format end time
		$endtime = $enddatearray['hours'];
		$endtime1 = $endtime - $offset1;
		$endtime2 = $endtime1 . ":00";
		$time1 = date_create($endtime2 );
		$endtime3 = date_format($time1, "g:i A"); 
//create new new MySignUpDataClass object with formatted slot data
		$obj = new MySignUpDataClass($weekday, $mysignup->startdatestring,$startdate1,$starttime3,$firstname,$lastname,$slotid,$endtime3);
//put new MySignUpDataClass object into array
		array_push($myArray, $obj);
	}
//return array of new MySignUpDataClass objects (slot info)

	return array($myArray,$curlerror);
}

//pull pickup slot data from SUG API
function pullpickupdata($startdate,$sid){
//get current date
	$formStartDate = $startdate;
//initialize curl		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://api.signupgenius.com/v2/k/signups/report/all/" . $sid . "/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	$headers = array();
	$headers[] = "Accept: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//execute curl
	$result = curl_exec($ch);
//check for curl errors
	if (curl_errno($ch)) {
		$curlerror = 'Error:' . curl_error($ch);
	} else {
		$curlerror = 'OK';
	}
//close curl connection
	curl_close ($ch);
//load json signup data into $signup array	
	$obj = json_decode($result, false);
	$data = $obj->data;
	$signup = $data->signup;
	$myArray = array();
//loop through signup data and format before putting into a MyPickUpDataClass object
	foreach ($signup as $mysignup) {
//set timezone to EST
		$offset = $mysignup->offset;
		$offset1 = substr($offset, 4, -3);
//format start date
		$startdate = strtotime($mysignup->startdatestring);
		$startdatearray = getDate($startdate);
		$startdate1 = date('m/d/Y', $startdate);
//add other data
		$weekday = $startdatearray['weekday'];
		$firstname = $mysignup->firstname;
		$lastname = $mysignup->lastname;
		$slotid = $mysignup->slotitemid;
//slot location is store name
		$slotlocation = $mysignup->item;
//put formatted slot data into new MyPickUpDataClass object
		$obj = new MyPickUpDataClass($weekday, $mysignup->startdatestring,$startdate1,$firstname,$lastname,$slotlocation,$slotid,);
//put MyPickUpDataClass MyPickUpDataClass into array
		array_push($myArray, $obj);
	}
//return array of MyPickUpDataClass slot objects and any curl errors
	//return array($signup,$myArray,$curlerror);
	return array($myArray,$curlerror);
}

//sort future slot dates
function sortfuturedates($myArray,$formStartDate,$vtype){
//sort MyPickUpDataClass objects by date objects by date
	usort($myArray, [MySignUpClass::class, "cmp_obj"]);
// Initialize an empty result arrays
	$sortedData = [];
	$filteredData = [];
//loop through MyPickUpDataClass slot array and select only current day or future slots
	foreach ($myArray as $arrSort) {
		$abc = strtotime($arrSort->mystartdate);
		$abc = date('Y-m-d', $abc);
		if (!empty($formStartDate)) {
			if ($abc >= $formStartDate) { 
			$filteredData[] = $arrSort;
			}
		}
	}
//return array of filtered MyPickUpDataClass objects
	return $filteredData;
}

//insert future slot data into db
function insertslotdata ($slotdata,$oslotdata,$dept,$ltype,$sid) {
//create variable of dept + slot type name
	$which = $dept . $ltype;

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
	
//set query text to delete all records newer than yesterday based on dept and slot type
	switch($which) {
		case "FPGC":
		$query = "delete from FPGC where slotdate >= '" . date('Y-m-d') . "'";
		break;
		case "FPPU":
		$query = "delete from FPPU where slotdate >= '" . date('Y-m-d') . "'";
		break;
	}
//run query
	mysqli_query($conn, $query);
	
//create sql query text based on dept and slot type
	switch($which) {
		case "FPGC":
//start of query string for slot data insertion
		$query = "INSERT INTO FPGC (slotid, slotdate, slotday, slotstarttime, slotfirst, slotlast, slotendtime) VALUES ";
//loop through slots and enter class variables into regular variables
		foreach ($slotdata as $sdata) {
			$sid = strval($sdata->myslotid);
			$sdate = date('Y-m-d',strtotime($sdata->mystartdate));
			$sday = $sdata->myweekday;
			$sstime = $sdata->mystarttime;
			$sfirst = strtolower($sdata->myfirstname);
			$slast = strtolower($sdata->mylastname);
//if first and last names are blank, mark as empty
			if ($sfirst == "" and $slast == "") {
				continue;
				//$sfirst = "***Empty***";
				//$slast = "***Slot***";
			}
//check to see if first name OR last name are blank
			if ($sfirst == "" or $slast == "") {
//if first name is present but not last, split name.
				if ($sfirst != "" and $slast == "") {
					$name = trim($sfirst);
					$slast = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
					$sfirst = trim( preg_replace('#'.preg_quote($slast,'#').'#', '', $name ) );
				}
//if last name is presernt but not first name, split name
				if ($sfirst == "" and $slast != "") {
					$name = trim($slast);
					$slast = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
					$sfirst = trim( preg_replace('#'.preg_quote($slast,'#').'#', '', $name ) );
				}
			}
		$setime = $sdata->myendtime;
//add new slot row data to the SQL statement

		$query = $query . "('". $sid . "','" . $sdate . "','" . $sday . "','" . $sstime . "','" . $sfirst . "','" . $slast . "','" . $setime . "'),";
		
		}
//add empty slots to db		
		foreach ($oslotdata as $sdata) {
			$sdate = date('Y-m-d',strtotime($sdata->mystartdate));
			$sday = $sdata->myweekday;
			
			$sstime = date_format(date_create($sdata->mystarttime), "g:i A");
			$setime = date_format(date_create($sdata->myendtime), "g:i A");
			$sfirst = "***Empty***";
			$slast = "***Slot***";
			$sid = $sdata->myslotid;
			$snumber = $sdata->myopenslots;
//add multiple slots for shift that have more than 1 missing volunteer
			for($i=0;$i<$snumber;$i++){
				$query = $query . "('". $sid . "','" . $sdate . "','" . $sday . "','" . $sstime . "','" . $sfirst . "','" . $slast . "','" . $setime . "'),";	
			}
		}
		break;
		
		case "FPPU":
//start of query string for slot data insertion
		$query = "INSERT INTO FPPU (slotid, slotdate, slotday, slotfirst, slotlast, slotlocation) VALUES ";
//loop through slots and enter class variables into regular variables
		foreach ($slotdata as $sdata) {
			$sid = strval($sdata->myslotid);
			$sdate = date('Y-m-d',strtotime($sdata->mystartdate));
			$sday = $sdata->myweekday;
			$sfirst = strtolower($sdata->myfirstname);
			$slast = strtolower($sdata->mylastname);
			$slocation = $sdata->myslotlocation;
//if first and last names are blank, mark as empty
			if ($sfirst == "" and $slast == "") {
				$sfirst = "***Empty***";
				$slast = "***Slot***";
			}
//check to see if first name OR last name are blank
			if ($sfirst == "" or $slast == "") {
//if first name is present but not last, split name.
				if ($sfirst != "" and $slast == "") {
					$name = trim($sfirst);
					$slast = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
					$sfirst = trim( preg_replace('#'.preg_quote($slast,'#').'#', '', $name ) );
				}
//if last name is presernt but not first name, split name
				if ($sfirst == "" and $slast != "") {
					$name = trim($slast);
					$slast = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
					$sfirst = trim( preg_replace('#'.preg_quote($slast,'#').'#', '', $name ) );
				}
			}
//add new row data to the SQL statement
		$query = $query . "('". $sid . "','" . $sdate . "','" . $sday . "','" . $sfirst . "','" . $slast . "','" . $slocation . "'),";
		}
		break;
	}
//remove the comma ofter the last row is added
	$query = rtrim($query, ",");
	echo $query;
//run query
	mysqli_query($conn, $query);
}

function sortalldates($myArray,$sdate){
//sort MySignUpClass objects by date
	usort($myArray, [MySignUpClass::class, "cmp_obj"]);
//initialize an empty result arrays
	$sortedData = [];
	$filteredData = [];
//loop through sorted MySignUpClass objects and push ones in date range to array
	foreach ($myArray as $arrSort) {
		$abc = strtotime($arrSort->mystartdate);
		$abc = date('Y-m-d', $abc);
		if (!empty($sdate)) {
			if ($abc >= $sdate) {
			$filteredData[] = $arrSort;
			}
		}
	}
	return $filteredData;
}

//Miriam's Signup class definition
class MySignUpClass {
	public $myweekday;
	public $mystartdatestring;
	public $mystartdate;
	public $mystarttime;
	public $myendtime;
	public $myopenslots;
	public $myitem;
	public $myslotid;

	public function __construct(string $a, string $b, string $c, string $d, string $e, int $f, string $g, int $h) {
		$this->myweekday = $a;
		$this->mystartdatestring = $b;
		$this->mystartdate = $c;
		$this->mystarttime = $d;
		$this->myendtime = $e;
		$this->myopenslots = $f;
		$this->myitem = $g;
		$this->myslotid = $h;
		}
		
	static function cmp_obj($a,$b) {
    return strtolower($a->mystartdatestring) <=> strtolower($b->mystartdatestring);
	}
}
	
//class for updating the in-building volunteer shifts
class MySignUpDataClass {
	public $myweekday;
	public $mystartdatestring;
	public $mystartdate;
	public $mystarttime;
	public $myfirstname;
	public $mylastname;
	public $myslotid;
	public $myendtime;


	public function __construct(string $a, string $b, string $c, string $d, string $e, string $f, int $g, string $h) {
		$this->myweekday = $a;
		$this->mystartdatestring = $b;
		$this->mystartdate = $c;
		$this->mystarttime = $d;
		$this->myfirstname = $e;
		$this->mylastname = $f;
		$this->myslotid = $g;
		$this->myendtime = $h;

		}
		
	static function cmp_obj($a,$b) {
		return strtolower($a->mystartdatestring) <=> strtolower($b->mystartdatestring);
	}
}

//Class for updating pickup shift data
class MyPickUpDataClass {
	public $myweekday;
	public $mystartdatestring;
	public $mystartdate;
	public $myfirstname;
	public $mylastname;
	public $myslotlocation;
	public $myslotid;



	public function __construct(string $a, string $b, string $c, string $d, string $e, string $f, int $g) {
		$this->myweekday = $a;
		$this->mystartdatestring = $b;
		$this->mystartdate = $c;
		$this->myfirstname = $d;
		$this->mylastname = $e;
		$this->myslotlocation = $f;
		$this->myslotid = $g;


		}
		
	static function cmp_obj($a,$b) {
		return strtolower($a->mystartdatestring) <=> strtolower($b->mystartdatestring);
	}
}

?>

