<?php
//include php code page
include 'code.php';

//main routine to send daily empty slot emails
function sendemail($depart,$sutype){

//get the start(current) date
	$sdate = getstartdate();

//get the end date
	$edate = getenddate($depart,$sutype);
	
//find signupid 
	$signupid = getsignupid(1,$depart,$sutype);

//pull data from SG API
	list($mArray,$cerror) = pulldata($signupid[0]);

//sort data by date
	$fdata = sortdates($mArray,$sdate,$edate,$sutype);	

//create message based on signup type
	$email = createmessage($fdata,$sdate,$edate,$signupid[1],$sutype);

//get Email addresses based on signup type
	$addresses = getemailaddresses($depart);

//send email
	emailinfo($email,$addresses,$depart,$sutype);
	//print_r($mArray);
}

//inital executing code

//FP Vounteers
$department = 'FP';
$signuptype = 'GC';
//sendemail($department,$signuptype);

//FP Couriers
$department = 'FP';
$signuptype = 'PU';
//sendemail($department,$signuptype);

/*
//CC Vounteers
$department = 'CC';
$signuptype = 'GC';
sendemail($department,$signuptype);

//CC Pickups
$department = 'CC';
$signuptype = 'PU';
sendemail($department,$signuptype);
*/

//write to log file
date_default_timezone_set("America/New_York");
$date = date('Y/m/d H:i:s');
$logtxt = "email sent on " . $date;
$result = file_put_contents('/var/www/html/emaillog.txt', $logtxt.PHP_EOL , FILE_APPEND | LOCK_EX);
?>

