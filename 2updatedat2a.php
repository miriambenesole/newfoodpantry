<?php
//include php code page
include 'code.php';
	
//main routine
function updatedata($depart,$sutype){
		
//get current date
	$sdate = getstartdate();
	
	$edate = getenddate($depart,$sutype);
		
//get signupid from db
	$signupid = getsignupid(1,$depart,$sutype);
		
//pull all data from signup
	if ($sutype == "GC") {
		list($mArray,$cerror) = pullinhousedata($sdate,$signupid[0]);
//pull open slot data
		list($openslots,$cerror)  = pulldata($signupid[0]);
//sort open slots
		$oslots = sortalldates($openslots,$sdate);
	} else {
		list($mArray,$cerror) = pullpickupdata($sdate,$signupid[0]);
		$oslots = null;
	}
//sort slot info by date
	$fdata = sortfuturedates($mArray,$sdate,$sutype);

//insert slot data from today on...
	insertslotdata($fdata,$oslots,$depart,$sutype,$signupid[0]);
	echo "Success for " . $depart,$sutype . "<br>";
}

//initial executable code

//FP vounteers
$department = 'FP';
$signuptype = 'GC';
updatedata($department,$signuptype);

//FP couriers
$department = 'FP';
$signuptype = 'PU';
updatedata($department,$signuptype);

//write to log file
date_default_timezone_set("America/New_York");
$date = date('Y/m/d H:i:s');
$logtxt = "update ran on " . $date;
$result = file_put_contents('/var/www/html/updatelog.txt', $logtxt.PHP_EOL , FILE_APPEND | LOCK_EX);
?>


