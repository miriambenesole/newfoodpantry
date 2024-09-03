<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="formstyles.css">
</head>

<body>
  <img src="GoochlandCares_whiteback.png">

  <h2>Volunteer Reports</h2>
  <h3>Select a group and click on button for next 5 business days or enter</h3>
  <h3>Start and End dates to report on volunteer slots that are still open for that period.</h3>
  <h3>Note: Click on calendar icons in each date field for easier selection.</h3>
  <h4>(This page looks better on Google Chrome)</h4>

 <table class="table1" margin=auto border=1 cellpadding=5px>

  <tr>
  <td class="selectiongroup">
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
      <b><label class="selecttext" for="group">1) Select a volunteer group:</label></b>
      <select name="group" id="group">
        <option value="">------ ------</option>
        <option value="Food Pantry Volunteers">Food Pantry Volunteers</option>
        <option value="Food Pantry Couriers">Food Pantry Couriers</option>
      </select>

      <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <b><input type="submit" id="next5days" name="next5days" class="submit" value="Next 5 business days"></b>
</b>
  <p class="selectdates"><b>or Select dates</p>
    <b><label for="startdate">Start Date:</label></b>
    <input type="date" class="formdates" id="startdate" name="startdate" value="" min="<?php echo date("Y-m-d"); ?>">
    <label class="padding" for="enddate">End Date:</label>
    <input type="date" class="formdates" id="enddate" name="enddate" value="" min="<?php echo date("Y-m-d"); ?>">
    <input type="submit" name="submit" id="submitclear" class="submit" value="Submit/Clear">
  </form>
  </br>
  </td>
  <tr>
	  <td class="selectiongroup">
    <div class="volunteer">
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <b><label class="volunteer" for="name">2) Enter a volunteer's first and last name:</label></b>
      <input class="volname" id="volunteername" type="text" name="name" value="">
      </br>
      <div class="volgroup">
      <b><label class="selectgroup1" for="group1">and select volunteer's group:</label></b>
      <select name="group1" id="group1">
        <option value="">------ ------</option>
        <option value="Food Pantry Volunteers">Food Pantry Volunteers</option>
        <option value="Food Pantry Couriers">Food Pantry Couriers</option>
      </select>
      <input type="submit" name="searchname" id="searchname" class="submit" value="Search/Clear">
      </div>
    </form>
    </div>
    </td>
  </tr>
</br>

  <?php
  require ('TrackerSignUpGroup.php');
  require ('FormDates.php');
  require ('PersonSignUp.php');
  require ('MySignUpClass.php');
  require ('SlotDateHelper.php');

  $databaseName = "FPGC";
  
  $emptySlotName = "***Empty*** ***Slot***";

  if (isset($_POST['submit'])) {
    $formStartDate = $_POST['startdate'];
    $formEndDate = $_POST['enddate'];
    $group = $_POST['group'];
  }

  if (isset($_POST['searchname'])) {
    $name = $_POST['name'];
    $group1 = $_POST['group1'];
    searchVolunteerSignUpsNew($name, $group1);
  }

  if (isset($_POST['submittraining'])) {
    getSignedUpSlots();
  }

  if (isset($_POST['next5days'])) {
    $group = $_POST['group'];
    $newFormDates = getNextFiveBusinessDaysToAndFrom();
    $formStartDate = $newFormDates->fromDate;
    $formEndDate = $newFormDates->toDate;
  }

  if (empty($group)) {
     //$alert = "PLEASE SELECT A GROUP";
		//echo "<script type='text/javascript'>alert('$alert');</script>";
  } else {
	  $databaseFields = " slotdate, slotstarttime ";
	  if (!str_contains($group, 'Volunteers')) {
		  $GLOBALS['databaseName'] = "FPPU";
		  $databaseFields = " slotdate ";
	  } 
	  /* retrieve empty slots dates from db */
	  $sqlstatement = "SELECT" . $databaseFields . "from " . $GLOBALS['databaseName']  . " WHERE slotfirst='***Empty***' AND slotdate BETWEEN " . "'$formStartDate'" . " AND " . "'$formEndDate'";
	  
	  $result = establishDBConnection($sqlstatement, false);
	  $emptySlotsDateTimeArray = array();
	  $emptySlotsDateArray = array();
	   if ($result->num_rows > 0) {
		   while($row = $result->fetch_assoc()) {
			   $emptydateTime = $row["slotdate"] . " " . $row["slotstarttime"];
			   array_push($emptySlotsDateTimeArray, $emptydateTime);
			   $emptydate = $row["slotdate"];
			   array_push($emptySlotsDateArray, $emptydate);
		   }
		} 
		else {
			if ($formStartDate == $formEndDate) {
              print "<p class=\"noresults\">";
              print "No open volunteer slots " . " on " . $formStartDate . ".";
              print "</p>";
            } else {
              print "<p class=\"noresults\">";
              print "No open volunteer slots " . " during " . $formStartDate . " and " . $formEndDate . ".";
              print "</p>";
            }
		}
	  $signuplist = getEmptySlotsVolunteers($emptySlotsDateArray, $group);
	   if (str_contains($group, 'Volunteers')) {
		   $transformedSignUpList = getFPGCTableList($emptySlotsDateArray, $signuplist);
		   } else {
			 $transformedSignUpList = getFPPUTableList($emptySlotsDateArray, $signuplist);
		   }
	  $emptySlotsTransformedList = calculateOpenSlots($transformedSignUpList, $group) ;
	  
	  $cleanedUpSlotList = cleanSlots($emptySlotsTransformedList);
	  getPrintedHeadings($group, $formStartDate, $formEndDate);
	   if (str_contains($group, 'Volunteers')) {
		 
		  getFPGCGroupPrinted($cleanedUpSlotList);
	  
	   } else {
		   getPickUpGroupPrinted($cleanedUpSlotList);
		   }
	 
	  print "</table>";
  }
  
  /*************** START OF FUNCTIONS ****************/
  
    /* Generic function to call database. */
    function establishDBConnection($sqlstatement, $shouldclose){
	//set db connection info
	$servername = "localhost";
	$username = "developer";
	$password = "1the600ch";
	$dbname = "signup";
	//create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	//check connection
	if ($conn->connect_error) {
		die("connection failed: " . $conn->connect_error);
		}
	//run query
	$result = $conn->query($sqlstatement);
	
	if ($shouldclose == true) {
		$conn->close();
	}
	return ($result);
	}
	
	/* This function gets volunteer data for dates between empty slots dates. */
	function getEmptySlotsVolunteers($emptySlotsDateArray, $group) {
	$minemptydate = min($emptySlotsDateArray);
	$maxemptydate = max($emptySlotsDateArray);
	$signuplist = array();
	if (!str_contains($group, 'Volunteers')) {
		  $databaseFields = " slotlocation ";
	  } else {
		  $databaseFields = " slotstarttime, slotendtime ";
		  
	  }
	$sqlstatement = "SELECT" . $databaseFields . ", slotdate, slotday, slotfirst, slotlast from " . $GLOBALS['databaseName']  . " WHERE slotdate BETWEEN " . "'$minemptydate'" . " AND " . "'$maxemptydate'";
	$result = establishDBConnection($sqlstatement, true);
	
	 if ($result->num_rows > 0) {
		while ($obj = $result->fetch_object()) {
			if (str_contains($group, 'Volunteers')) {
				$mysignup = new PersonSignUp($obj->slotfirst, $obj->slotlast, $obj->slotday, $obj->slotdate, 
			$obj->slotstarttime, $obj->slotendtime,"Assist clients/sort and stock donations");
			}
			else {
				$mysignup = new PersonSignUp($obj->slotfirst, $obj->slotlast, $obj->slotday, $obj->slotdate, 
			"", "", $obj->slotlocation);
				}
			array_push($signuplist, $mysignup);
	}
		} 
		else {
			echo "0 results";
		}
	return $signuplist;
}

	/* This function obtains volunteer data in the appropriate format for Food Pantry Volunteers that work 
	 at Goochland Cares location. */
	function getFPGCTableList($emptySlotsDateArray, $signuplist) {
	  $transformedSignUpList = array();
	  $filteredList = array();
	  $dateTimeArrayKeys = array();
	  
	  /* Get days that have empty slots */
	  foreach($emptySlotsDateArray as $emptyDate) {
		  foreach($signuplist as $signupPerson) {
			  if ($signupPerson->startdate == $emptyDate) {
				  array_push($dateTimeArrayKeys, $signupPerson->startdate . " " . $signupPerson->starttime);
				  array_push($filteredList, $signupPerson);
				  }
	  }
	  }
	  $dateTimeArrayKeys = array_unique($dateTimeArrayKeys);
	  
	  foreach ($dateTimeArrayKeys as $dateTimeInstance) {
		  $peopleInSlot = array();
		  foreach($signuplist as $signupPerson) {
			   if ($signupPerson->startdate . " " . $signupPerson->starttime == $dateTimeInstance) {
				   array_push($peopleInSlot, ucfirst($signupPerson->firstname) . " " . ucfirst($signupPerson->lastname));
				   $slotDateHelp = new SlotDateHelper($signupPerson->weekday, $signupPerson->startdate, $signupPerson->starttime, $signupPerson->endtime, $signupPerson->item);
			  } 
		  }
			   $slotSignUp = new MySignUpClass($slotDateHelp->item, $slotDateHelp->weekday, $slotDateHelp->startdate, 
		 $slotDateHelp->starttime, $slotDateHelp->endtime, 0, $peopleInSlot);
		 array_push($transformedSignUpList, $slotSignUp);
		  }

	    return $transformedSignUpList;
}

	/* This function obtains volunteer data in the appropriate format for Food Pantry Pick Up Volunteers. */
	function getFPPUTableList($emptySlotsDateArray, $signuplist) {
	  $transformedSignUpList = array();
	  $filteredList = array();
	  $dateItemArrayKeys = array();
	  
	  foreach($emptySlotsDateArray as $emptyDate) {
		  foreach($signuplist as $signupPerson) {
			  if ($signupPerson->startdate == $emptyDate) {
				  array_push($dateItemArrayKeys, $signupPerson->startdate . $signupPerson->item);
				  array_push($filteredList, $signupPerson);
				  }
	  }
	  }
	  
	  $dateItemArrayKeys = array_unique($dateItemArrayKeys);
	  
	  foreach ($dateItemArrayKeys as $dateItemInstance) {
		  $peopleInSlot = array();
		  foreach($signuplist as $signupPerson) {
			   if ($signupPerson->startdate . $signupPerson->item == $dateItemInstance) {
				   array_push($peopleInSlot, ucfirst($signupPerson->firstname) . " " . ucfirst($signupPerson->lastname));
				   $slotDateHelp = new SlotDateHelper($signupPerson->weekday, $signupPerson->startdate, $signupPerson->starttime, $signupPerson->endtime, $signupPerson->item);
			  } 
		  }
			   $slotSignUp = new MySignUpClass($slotDateHelp->item, $slotDateHelp->weekday, $slotDateHelp->startdate, 
		 $slotDateHelp->starttime, $slotDateHelp->endtime, 0, $peopleInSlot);
		 array_push($transformedSignUpList, $slotSignUp);
		  }

	    return $transformedSignUpList;
  }

	 /* This function calculates how many open slots there are per shift */
	  function calculateOpenSlots($transformedSignUpList, $group) {
	  $emptySlotsTransformedList = array();
	  foreach ($transformedSignUpList as $slotEntry) {
		  $i = 0;
		  foreach ($slotEntry->myvolunteers as $volunteer) {
			   if ($volunteer ===  $GLOBALS['emptySlotName']) {
			  $i++;
		  }
		  if (str_contains($group, 'Volunteers')) {
			   $transformedSlot = new MySignUpClass($slotEntry->myitem, $slotEntry->myweekday, 
			 $slotEntry->mystartdate, $slotEntry->mystarttime, $slotEntry->myendtime, $i, $slotEntry->myvolunteers);
		  } else {
			  $transformedSlot = new MySignUpClass($slotEntry->myitem, $slotEntry->myweekday, 
			 $slotEntry->mystartdate, "", "", $i, $slotEntry->myvolunteers);
		  }
			 
		  }
		  array_push($emptySlotsTransformedList, $transformedSlot);
	  }
	return $emptySlotsTransformedList;
	  
  }
  
  /* This function removes the entries 'Empty Slot' */
  function cleanSlots($emptySlotsTransformedList) {
	  $cleanedUpSlotList = array();
	  foreach ($emptySlotsTransformedList as $slotEntry) {
		  $newarray = array_diff($slotEntry->myvolunteers, [$GLOBALS['emptySlotName']]);
		  $slotEntry->myvolunteers = $newarray;
		  
		  array_push($cleanedUpSlotList, $slotEntry);
	  }

	  return $cleanedUpSlotList;
	  }
  
  /* This function prints table heading for display. */
  function getPrintedHeadings($group, $formStartDate, $formEndDate)
  {
	print "<table margin=auto border=1 cellpadding=5px>";  
    print "<th class=\"topheading\" colspan=5 text-align=center><b>";
    print $group;
    print "</br>";
    print "<p id=\"resulttitle\">" . $formStartDate . " " . " to  " . $formEndDate . "</p>";
    print "</b></th>";
    print "</p>";
    print "</h2>";
    print "<tr>";
    print "<th align=center><b>";
    print "Date";
     if (str_contains($group, 'Volunteers')) {
      print "<th align=center><b>";
      print "Time Slot";
      print "</b></th>";
    }
    print "<th align=center><b>";
    print "Item";
    print "</b></th>";
    print "<th align=center><b>";
    print "Open Slots";
    print "</b></th>";
    print "<th align=center><b>";
    print "Volunteers signed up";
    print "</b></th>";
    print "</tr>";
  }
  
  /* This function prints volunteer data in the appropriate format for Food Pantry Volunteers that work 
	 at Goochland Cares location. */
   function getFPGCGroupPrinted($cleanedUpSlotList)
  {
	$storedDate = "";
	foreach($cleanedUpSlotList as $filteredItem) {
    print "<tr>";
    if ($filteredItem->myweekday . ", " . $filteredItem->mystartdate == $storedDate) {
		$prtdt = "";
		} else {
			$prtdt = $filteredItem->myweekday . ", " . $filteredItem->mystartdate;
			}
    print "<td class=\"result\" id=\"result\"  align=center>";
    print $prtdt;
    
    print "</td>";
      print "<td class=\"result\" align=center>";
      print $filteredItem->mystarttime . " - " . $filteredItem->myendtime;
      print "</td>";
    print "<td class=\"result\" align=center>";
    print $filteredItem->myitem;
    print "</td>";
    if ($filteredItem->myopenslots > 1) {
      print "<b>";
      print "<td class=\"bigresult\" align=center>";
      print $filteredItem->myopenslots;
      print "</td>";
      print "</b>";
    } else {
      print "<td class=\"result\" align=center>";
      print $filteredItem->myopenslots;
      print "</td>";
    }
    print "<td class=\"result\" align=center>";
    foreach ($filteredItem->myvolunteers as $volunteerName) {
      print $volunteerName;
      print "<br>";
    }
    print "</td>";
    $storedDate = $filteredItem->myweekday . ", " . $filteredItem->mystartdate;

    print "</tr>";
}
  }
  
  /* This function prints volunteer data in the appropriate format for Food Pantry Pick Up Volunteers. */
  function getPickUpGroupPrinted($cleanedUpSlotList) {
	  foreach($cleanedUpSlotList as $slot)
		{ 
		$categories[$slot->mystartdate][] = $slot;
		}
		$arrayKeys = array_keys($categories);
		
		/* Print first row */
		foreach($arrayKeys as $key) {
			print "<tr>";
			$prtdte = $key;
		
		foreach ($categories[$key] as $value) {
			print "<td class=\"result\" id=\"result\"  align=center>";
		print $prtdte;
		print "</td>"; 
			print "<td class=\"result\" align=center>";
		print $value->myitem;
		print "</td>";
		if ($value->myopenslots >= 1) {
		  print "<b>";
		  print "<td class=\"bigresult\" align=center>";
		  print $value->myopenslots;
		  print "</td>";
		  print "</b>";
		} else {
		  print "<td class=\"result\" align=center>";
		  print $value->myopenslots;
		  print "</td>";
		}
		print "<td class=\"result\" align=center>";
		foreach ($value->myvolunteers as $volunteerName) {
		  print $volunteerName;
		  print "</td>";
		}
		print "</tr>";
		$prtdte = "";
			}
			}
  }
  
  /* This function searches for volunteer's data in "surrounding dates" - 10 days before today, today and 10 days after today. */
  function searchVolunteerSignUpsNew($name, $group1) {
    $surroundingDates = getSurroundingBusinessDays();
    $minDate = min($surroundingDates);
    $maxDate = max($surroundingDates);
    $personSignUpArray = array();
    
    /* Split full name. */
    $firstName = strtolower(trim(substr($name, 0, strpos($name, ' ')), " "));
	$lastName = strtolower(trim(substr($name, strlen($firstName)), " "));
    
	 if (!str_contains($group1, 'Volunteers')) {
		 $GLOBALS['databaseName'] = "FPPU";
		  $databaseFields = " slotlocation ";
	  } else {
		  $databaseFields = " slotstarttime, slotendtime ";
	  }
	  
	$sqlstatement = "SELECT" . $databaseFields . ", slotdate, slotday, slotfirst, slotlast from " . $GLOBALS['databaseName']  . 
	" WHERE slotdate BETWEEN " . "'$minDate'" . " AND " . "'$maxDate'" . " AND slotfirst = " . "'$firstName'" . " AND slotlast = " . "'$lastName'";
	
	$result = establishDBConnection($sqlstatement, true);
	
	if ($result->num_rows > 0) {
		while ($obj = $result->fetch_object()) {
			if (str_contains($group1, 'Volunteers')) {
				$mysignup = new PersonSignUp($obj->slotfirst, $obj->slotlast, $obj->slotday, $obj->slotdate, 
			$obj->slotstarttime, $obj->slotendtime,"Assist clients/sort and stock donations");
			}
			else {
				$mysignup = new PersonSignUp($obj->slotfirst, $obj->slotlast, $obj->slotday, $obj->slotdate, 
			"", "", $obj->slotlocation);
				}
			array_push($personSignUpArray, $mysignup);
	}
		printVolunteer($personSignUpArray, $group1, $name);
		} else {
	  print "<p class=\"nosignups\">";
      print "No sign ups for " . $name . " between " . min($surroundingDates) . " and " . max($surroundingDates) . ".";
      print "</p>";
		}
		
	
  }
  
  /* This function prints the specific volunteer's data in "surrounding dates" - 10 days before today, today and 10 days after today. */
  function printVolunteer($personSignUpArray, $searchGroup, $name) {
    print "<table class=\"table2\" margin=auto border=1 cellpadding=5px>";
    print "<th colspan=5 text-align=center><b>";
    print $name;
    print "</b></th>";
    print "</p>";
    print "</h2>";
    print "<tr>";
    print "<th align=center><b>";
    print "Item";
    print "<th align=center><b>";
    print "Date";
    print "</b></th>";
    if (str_contains($searchGroup, 'Volunteers')) {
      print "<th align=center><b>";
      print "Time Slot";
      print "</b></th>";
    }
    print "</tr>";
    foreach ($personSignUpArray as $grpElement) {
      print "<tr>";
      print "<td class=\"result\" id=\"result\"  align=center>";
      print $grpElement->item;
      print "</td>";
      print "<td class=\"result\" align=center>";
      print $grpElement->startdate;
      print "</td>";
      if (str_contains($searchGroup, 'Volunteers')) {
        print "<td class=\"result\" align=center>";
        print $grpElement->starttime . " - " . $grpElement->endtime;
        print "</td>";
      }
      print "</tr>";
    }
    print "</table>";
  }
  
  /* This function returns an array of dates - from 10 business days ago 
  to 10 business days in the future. Note - does not take holidays into account. */
  function getSurroundingBusinessDays() {
    $surroundingDates = array();
    $todaydate = date('Y-m-d');
    $pastDates = getWorkingDays($todaydate, true);
    array_push($surroundingDates, $pastDates);
    $futureDates = getWorkingDays($todaydate, false);
    array_push($surroundingDates, $futureDates);
    $surroundingDates = combineArrays($surroundingDates);
    array_push($surroundingDates, $todaydate);
    
    return $surroundingDates;

  }

  /* This function returns 10 days forward or backwards. */
  function getWorkingDays($date, $backwards)
{
    $working_days = array();
    do
    {
        $direction = $backwards ? 'last' : 'next';
        $date = date("Y-m-d", strtotime("$direction weekday", strtotime($date)));
            $working_days[] = $date;
    }
    while (count($working_days) < 10);
    
    return $working_days;
}

 /* This function combines an array of arrays into one array with the sum of the arrays. */
  function combineArrays($myArray1)
  {
    $myArray11 = array();
    foreach ($myArray1 as $thisArray) {
      foreach ($thisArray as $value1) {
        array_push($myArray11, $value1);
      }
    }
    return $myArray11;
  }
  
  /* This is a helper function that gets the min and max dates for the next 5 business days. */  
  function getNextFiveBusinessDaysToAndFrom()
  {
    $dates = array();
    $date = new DateTime();

    while (count($dates) < 5) {
      $date->add(new DateInterval('P1D'));
      if ($date->format('N') < 6)
        $dates[] = $date->format('Y-m-d');
    }

    return new FormDates(min($dates), max($dates));
  }


  ?>

</body>

</html>
