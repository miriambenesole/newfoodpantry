<!doctype html>
<html>

<head>
  <link rel="stylesheet" href="formstyles.css">
</head>

<body>
  <img src="goochlandcares_whiteback.png">

  <h2>volunteer reports</h2>
  <h3>select a group and click on button for next 5 business days or enter</h3>
  <h3>start and end dates to report on volunteer slots that are still open for that period.</h3>
  <h3>note: click on calendar icons in each date field for easier selection.</h3>
  <h4>(this page looks better on google chrome)</h4>


<table>
  <td>
  <tr>
    <form action="<?= $_server['php_self'] ?>" method="post">
      <b><label class="selecttext" for="group">1) select a volunteer group:</label></b>
      <select name="group" id="group">
        <option value="">------ ------</option>
        <option value="food pantry volunteers">food pantry volunteers</option>
        <option value="food pantry couriers">food pantry couriers</option>
        <option value="clothes closet volunteers">clothes closet volunteers</option>
        <option value="clothes closet couriers">clothes closet couriers</option>
        <option value="all">all</option>
      </select>

      <form action="<?= $_server['php_self'] ?>" method="post">
        <b><input type="submit" id="next5days" name="next5days" class="submit" value="next 5 business days"></b>
  </tr>
  <p class="selectdates"><b>or</b> select dates</p>
  <tr>
    <b><label for="startdate">start date:</label></b>
    <input type="date" class="formdates" id="startdate" name="startdate" value="" min="<?php echo date("y-m-d"); ?>">
    <label class="padding" for="enddate">end date:</label>
    <input type="date" class="formdates" id="enddate" name="enddate" value="" min="<?php echo date("y-m-d"); ?>">
    <input type="submit" name="submit" id="submitclear" class="submit" value="submit/clear">
  </tr>
  </form>
  </br>
  </td>
  <tr>
    <div class="volunteer">
    <form action="<?= $_server['php_self'] ?>" method="post">
    <b><label class="volunteer" for="name">2) enter a volunteer's first and last name:</label></b>
      <input class="volname" id="volunteername" type="text" name="name" value="">
      </br>
      <div class="volgroup">
      <b><label class="selectgroup1" for="group1">and select volunteer's group:</label></b>
      <select name="group1" id="group1">
        <option value="">------ ------</option>
        <option value="food pantry volunteers">food pantry volunteers</option>
        <option value="food pantry couriers">food pantry couriers</option>
        <option value="clothes closet volunteers">clothes closet volunteers</option>
        <option value="clothes closet couriers">clothes closet couriers</option>
      </select>
      <input type="submit" name="searchname" id="searchname" class="submit" value="search/clear">
      </div>
    </form>
    </div>
  </tr>
</br>

  <!-- view upcoming training -->
  <!-- <form action="<?= $_server['php_self'] ?>" method="post">
    <label class="foodtraining">3) upcoming food pantry training:</label>

    <input type="submit" id="submittraining" name="submittraining" class="submit" value="view">
  </form>
  </br> -->
  </table>

  <?php
  require ('signupgroupclass.php');
  require('mysignupclass.php');
  require ('signupitem.php');
  require ('signupgroupsearchclass.php');
  require ('trackersignupgroup.php');
  require ('formdates.php');
  require ('personsignup.php');
  require ('emptyslot.php');
  include ('trainingsignups.php');

  global $signupidsearcharray2;

  if (isset($_post['submit'])) {
    $formstartdate = $_post['startdate'];
    $formenddate = $_post['enddate'];
    $group = $_post['group'];
  }

  if (isset($_post['searchname'])) {
    $name = $_post['name'];
    $group1 = $_post['group1'];
    searchvolunteersignups($name, $group1);
  }

  if (isset($_post['submittraining'])) {
    getsignedupslots();
  }

  if (isset($_post['next5days'])) {
    $group = $_post['group'];
    $newformdates = getnextfivebusinessdaystoandfrom();
    $formstartdate = $newformdates->fromdate;
    $formenddate = $newformdates->todate;
  }

  if (empty($group)) {
    // print "please select a group";
  } else {
	  /* retrieve empty slots dates from db */
	  $sqlstatement = "select distinct slotdate from fpgc where slotfirst='***empty***'" . " and slotdate between " . "'$formstartdate'" . " and " . "'$formenddate'";
	
	  $result = establishdbconnection($sqlstatement, false);
	   if ($result->num_rows > 0) {
		$row = $result ->fetch_all(mysqli_assoc);
		} 
		else {
			echo "0 results";
		}
	$emptyslotsdates = array_column($row, 'slotdate');
	//var_dump($emptyslotsdates);
	  getemptyslotsvolunteers($emptyslotsdates);
	  //var_dump($emptyslotsdates);
	  //$allvolunteers = convertobject($allslots);
	  //getprintedheadings($group);
	  //getgroupprinted($group, $signups);
	  //converttogroup($allvolunteers);
  }
  
  	 function establishdbconnection($sqlstatement, $shouldclose){
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

function getemptyslotsvolunteers($emptyslotsdates) {
	$minemptydate = min($emptyslotsdates);
	$maxemptydate = max($emptyslotsdates);
	$signuplist = array();
	$sqlstatement = "select slotdate, slotday, slotstarttime, slotendtime, slotfirst, slotlast from fpgc where slotdate between " . "'$minemptydate'" . " and " . "'$maxemptydate'";
	//var_dump($sqlstatement);
	$result = establishdbconnection($sqlstatement, false);
	//var_dump($emptyslotdaysvolunteers);
	 if ($result->num_rows > 0) {
		while ($obj = $result->fetch_object()) {
			$mysignup = new personsignup($obj->slotfirst, $obj->slotlast, $obj->slotday, $obj->slotdate, 
			$obj->slotstarttime, $obj->slotendtime,"assist with donations");
			array_push($signuplist, $mysignup);
	}
		} 
		else {
			echo "0 results";
		}
	return $signuplist;
}

function convertobject($allslots) {
	$allvolunteers = array();
	foreach ($allslots as $volunteerslot) {
		$volunteer = new personsignup($volunteerslot['slotfirst'], $volunteerslot['slotlast'], $volunteerslot['slotday'], 
		$volunteerslot['slotdate'], $volunteerslot['slotstarttime'], $volunteerslot['slotendtime'], "sort values");
		array_push($allvolunteers, $volunteer);
	}
	var_dump($allvolunteers);
	return $allvolunteers;
}

  /* this function retrieves sign ups for an array of signupids. 
  because each api call is fired in parallel - this is more  
  efficient than making one call at a time. all resuts are returned in an array. 
  this function can be used to call signupgenius open slots api and filled slots api*/
  function getparallelvolunteerslots($option, $signupidsearcharray2)
  {
    $node_count = count($signupidsearcharray2);
    $master = curl_multi_init();

    for ($i = 0; $i < $node_count; $i++) {
      $signupid = $signupidsearcharray2[$i];
      $url = "https://api.signupgenius.com/v2/k/signups/report/" . $option . "/" . $signupid . "/?user_key=ry84b1vjaefdzujvywo4t2jvbexzdz09";
      $curl_arr[$i] = curl_init($url);
      curl_setopt($curl_arr[$i], curlopt_returntransfer, true);
      curl_setopt($curl_arr[$i], curlopt_customrequest, "get");

      $headers2 = array();
      $headers2[] = "accept: application/json";
      curl_setopt($curl_arr[$i], curlopt_httpheader, $headers2);
      curl_multi_add_handle($master, $curl_arr[$i]);
    }
    do {
      usleep(10000);
      curl_multi_exec($master, $running);
    } while ($running > 0);

    for ($i = 0; $i < $node_count; $i++) {
      curl_multi_remove_handle($master, $curl_arr[$i]);
      $results[] = curl_multi_getcontent($curl_arr[$i]);
    }
    curl_multi_close($master);
    return $results;
  }

  /* this function is used for extracting the desired datasets. */
  function geteachresult($result2)
  {
    $obj3 = json_decode($result2, false);
    if (property_exists($obj3, 'data')) {
      $data3 = $obj3->data;
      if (property_exists($data3, 'signup')) {
        return $data3->signup;
      } else {
        print "signup genius api call failed.";
        exit;
      }
    } else {
      print "signup genius api call failed.";
      exit;
    }
  }

  function getsignupdata($data) {
    $myarray = array();
    foreach ($data as $mydata) {
      $title = $mydata->title;
      $signupstartdatestring = $mydata->startdatestring;
      $signupenddatestring = $mydata->enddatestring;
      $signupid = $mydata->signupid;
      $obj = new signupgroupclass($signupid, $title, $signupstartdatestring, $signupenddatestring);
      array_push($myarray, $obj);
    }
    return $myarray;
  }

  function searchvolunteersignups($name, $group1) {
    $signupidsearcharray = array();
    $data = getsignupids();
    $myarray = getsignupdata($data);

    foreach ($myarray as $mygroup) {
      if (
        str_contains($mygroup->gptitle, 'food pantry') && !str_contains($mygroup->gptitle, 'training') || str_contains($mygroup->gptitle, 'grocery') ||
        str_contains($mygroup->gptitle, 'clothes closet')
      ) {
        $a = $mygroup->gpsignupid;
        $b = $mygroup->gptitle;
        $c = $mygroup->gpstartdatestring;
        $d = $mygroup->gpenddatestring;
        $signupidsearchelement = new signupgroupclass($a, $b, $c, $d);
        array_push($signupidsearcharray, $signupidsearchelement);
      }
    }

    $todaydate = date('y-m-d');
    $surroundingdates = getsurroundingbusinessdays();

    $signupidsearcharray2 = categorizesignups($signupidsearcharray, $todaydate);
    
    if (!empty($signupidsearcharray2) || !is_null($signupidsearcharray)) {
      $signupsarray = array();
      foreach ($signupidsearcharray2 as $signupidsearch2) {
        $signupidforsearch = $signupidsearch2->searchsignupid;
        array_push($signupsarray, $signupidforsearch);
      }
      $filledsignups = getparallelvolunteerslots("filled", $signupsarray);

      $fillednamedgroups = getnamedgroups($filledsignups, $signupidsearcharray2);

      $filledcombinedgroups = getcombinedgroups($fillednamedgroups);
    }
    $filtereddataind = array();

    foreach($filledcombinedgroups as $combinedgroup) {
      if ($combinedgroup->signupgroupname == $group1) {
        $grouparray = $combinedgroup->grouparray;
        $signupidgroup = array();
        foreach( $grouparray as $result) {
          $firstname = $result->firstname;
          $lastname = $result->lastname;
          $item = $result->item;
          $offset = $result->offset;
          $offset1 = substr($offset, 4, -3);
          $startdate = strtotime($result->startdatestring);
          $startdatearray = getdate($startdate);
          $formattedstartdate = date('l, m/d/y', $startdate);
          $enddatestring = $result->enddatestring;
          $enddate = strtotime($enddatestring);
          $enddatearray = getdate($enddate);
          $formattedstarttime = getformattedtime($offset1, $startdatearray);

          $formattedendtime = getformattedtime($offset1, $enddatearray);

          $weekday = $startdatearray['weekday'];
          /* signupitem is an object that holds only the fields of interest for each sign up.*/
          $obj3 = new personsignup($firstname, $lastname, $weekday, $result->startdatestring, $formattedstartdate, $formattedstarttime, $formattedendtime, $item);
          array_push($signupidgroup, $obj3);
        }
        foreach ($signupidgroup as $signupitem) {
          /* for each item, get the start date in the desired format for comparison with the form's start and end dates. */
          $abc = strtotime($signupitem->startdate);
          $abc = date('y-m-d', $abc);
          if ($abc >= min($surroundingdates) && $abc <= max($surroundingdates)) {
            array_push($filtereddataind, $signupitem);
          }
        }
      }
    }

    $personsignuparray = array();
  
    foreach($filtereddataind as $dataind) {
      $fullname = $dataind->firstname . " " . $dataind->lastname;
      if($fullname == $name) {
        array_push($personsignuparray, $dataind);
      }
    }
    usort($personsignuparray, [personsignup::class, "cmp_obj"]);

    if(!empty($personsignuparray)) {
    printvolunteer($personsignuparray, $group1, $name);
    } else {
      print "<p class=\"nosignups\">";
      print "no sign ups for " . $name . " between " . min($surroundingdates) . " and " . max($surroundingdates) . ".";
      print "</p>";
    }
  }

  function printvolunteer($personsignuparray, $searchgroup, $name) {
    print "<table margin=auto border=1 cellpadding=5px>";
    print "<th colspan=5 text-align=center><b>";
    print $name;
    print "</b></th>";
    print "</p>";
    print "</h2>";
    print "<tr>";
    print "<th align=center><b>";
    print "item";
    print "<th align=center><b>";
    print "date";
    print "</b></th>";
    if (str_contains($searchgroup, 'volunteers')) {
      print "<th align=center><b>";
      print "time slot";
      print "</b></th>";
    }
    print "</tr>";
    foreach ($personsignuparray as $grpelement) {
      print "<tr>";
      print "<td class=\"result\" id=\"result\"  align=center>";
      print $grpelement->item;
      print "</td>";
      print "<td class=\"result\" align=center>";
      print $grpelement->startdate;
      print "</td>";
      if (str_contains($searchgroup, 'volunteers')) {
        print "<td class=\"result\" align=center>";
        print $grpelement->starttime . " - " . $grpelement->endtime;
        print "</td>";
      }
      print "</tr>";
    }
    print "</table>";
  }

  /* this function returns an array of dates - from 10 business days ago 
  to 10 business days in the future. note - does not take holidays into account. */
  function getsurroundingbusinessdays() {
    $surroundingdates = array();
    $todaydate = date('y-m-d');
    $pastdates = getworkingdays($todaydate, true);
    array_push($surroundingdates, $pastdates);
    $futuredates = getworkingdays($todaydate, false);
    array_push($surroundingdates, $futuredates);
    $surroundingdates = combinearrays($surroundingdates);
    array_push($surroundingdates, $todaydate);
    
    return $surroundingdates;

  }

  /* this function returns 10 days forward or backwards. */
  function getworkingdays($date, $backwards)
{
    $working_days = array();
    do
    {
        $direction = $backwards ? 'last' : 'next';
        $date = date("y-m-d", strtotime("$direction weekday", strtotime($date)));
            $working_days[] = $date;
    }
    while (count($working_days) < 10);

    return $working_days;
}

/* this function takes an array and organizes them into one of the 4 groups. it also filters for the dates of 
intrest. */
  function categorizesignups($signupidsearcharray, $formstartdate) {
    $signupidsearcharray2 = array();
    foreach ($signupidsearcharray as $signupidsearch) {
      $groupresultstartdate = strtotime($signupidsearch->gpstartdatestring);
      $groupresultstartdate = date('y-m-d', $groupresultstartdate);
      $groupresultenddate = strtotime($signupidsearch->gpenddatestring);
      $groupresultenddate = date('y-m-d', $groupresultenddate);
      $groupresultendtitle = $signupidsearch->gptitle;
      /* if group for search falls within the requested start date or greater. */
      if ($groupresultenddate >= $formstartdate) {
        /* keep the signupid connected to the group of interest in a signupgroupsearchclass object, which connects each signup id with the group of interest.  */
        if (str_contains($groupresultendtitle, 'food pantry') && !str_contains($groupresultendtitle, 'training')) {
          $mysearch = new signupgroupsearchclass("food pantry volunteers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'grocery')) {
          $mysearch = new signupgroupsearchclass("food pantry couriers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'clothes closet bin')) {
          $mysearch = new signupgroupsearchclass("clothes closet couriers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'clothes closet')) {
          $mysearch = new signupgroupsearchclass("clothes closet volunteers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        }
      }
    }

    return $signupidsearcharray2;
  }

  /* this function returns the correlation between group name and its array of results. */
  function getnamedgroups($parallelopensignups, $signupidsearcharray2)
  {
    $namedgroups = array();

    foreach ($parallelopensignups as $result2) {
      $resultarray = geteachresult($result2);
      $groupsignupid = $resultarray[0]->signupid;

      /* keep track of the 4 categories 
      signupidsearcharray2 kept track of each sign up id to group */
      foreach ($signupidsearcharray2 as $signupidsearch) {
        if ($signupidsearch->searchsignupid == $groupsignupid) {
          $namedmatch = $signupidsearch->signupgroup;
          /* trackersignupgroup objects will correlate group name (ex: "food pantry volunteers") to result array. */
          $namedsignupgroup = new trackersignupgroup($namedmatch, $resultarray);
        }
      }
      array_push($namedgroups, $namedsignupgroup);
    }

    return $namedgroups;
  }

  function getcombinedgroups($namedgroups)
  {
    $myarray1 = array();
    $myarray2 = array();
    $myarray3 = array();
    $myarray4 = array();
    /* several trackersignupgroups were created, potentially for the same category group - so we need to put them together 
    unfortunately, we first need to combine results of each category 
    this is where the logic to gather multiple sign up lists in order to process them starts. */
    foreach ($namedgroups as $groupelement) {
      if ($groupelement->signupgroupname == "food pantry volunteers") {
        array_push($myarray1, $groupelement->grouparray);
      }
      if ($groupelement->signupgroupname == "food pantry couriers") {
        array_push($myarray2, $groupelement->grouparray);
      }
      if ($groupelement->signupgroupname == "clothes closet volunteers") {
        array_push($myarray3, $groupelement->grouparray);
      }
      if ($groupelement->signupgroupname == "clothes closet couriers") {
        array_push($myarray4, $groupelement->grouparray);
      }
    }
    /* now we need to have 1 trackersignupgroup object for each category in order to print them.
    once we have a single  trackersignupgroup to an array of sign ups, we will be able to print them.  
    trackersignupgroup object keeps track of group name to an array. */
    $combinedgroups = array();
    $namedsignupgroup1 = new trackersignupgroup("food pantry volunteers", combinearrays($myarray1));
    array_push($combinedgroups, $namedsignupgroup1);
    $namedsignupgroup3 = new trackersignupgroup("clothes closet volunteers", combinearrays($myarray3));
    array_push($combinedgroups, $namedsignupgroup3);
    $namedsignupgroup2 = new trackersignupgroup("food pantry couriers", combinearrays($myarray2));
    array_push($combinedgroups, $namedsignupgroup2);
    $namedsignupgroup4 = new trackersignupgroup("clothes closet couriers", combinearrays($myarray4));
    array_push($combinedgroups, $namedsignupgroup4);

    return $combinedgroups;
  }
  /* this function combines an array of arrays into one array with the sum of the arrays. */
  function combinearrays($myarray1)
  {
    $myarray11 = array();
    foreach ($myarray1 as $thisarray) {
      foreach ($thisarray as $value1) {
        array_push($myarray11, $value1);
      }
    }
    return $myarray11;
  }

  function getformattedtime($offset1, $datearray)
  {
    $starttime = $datearray['hours'];
    $starttime1 = $starttime - $offset1;
    $starttime2 = $starttime1 . ":" . $datearray['minutes'];
    $time = date_create($starttime2);
    $formattedstarttime = date_format($time, "h:ia");

    return $formattedstarttime;
  }

  function getprintedheadings($group)
  {
	print "<table margin=auto border=1 cellpadding=5px>";  
    print "<th colspan=5 text-align=center><b>";
    print $group;
    print "</b></th>";
    print "</p>";
    print "</h2>";
    print "<tr>";
    print "<th align=center><b>";
    print "item";
    print "<th align=center><b>";
    print "date";
    print "</b></th>";
    if (str_contains($group, 'volunteers')) {
      print "<th align=center><b>";
      print "time slot";
      print "</b></th>";
    }
    print "<th align=center><b>";
    print "open slots";
    print "</b></th>";
    print "<th align=center><b>";
    print "volunteers signed up";
    print "</b></th>";
    print "</tr>";
    print "</table>";
  }


  function getgroupprinted($group, $signups)
  {
	  foreach($signups as $signup) {
		  print "<tr>";
    print "<td class=\"result\" id=\"result\"  align=center>";
    print $signup->item;
    print "</td>";
    print "<td class=\"result\" align=center>";
    print $signup->startdate;
    print "</td>";
    if (str_contains($group, 'volunteers')) {
      print "<td class=\"result\" align=center>";
      print $signup->starttime . " - " . $signup->endtime;
      print "</td>";
    }
    //if ($grpelement->myopenslots > 1) {
      //print "<b>";
      //print "<td class=\"bigresult\" align=center>";
      //print $grpelement->myopenslots;
      //print "</td>";
      //print "</b>";
    //} else {
      print "<td class=\"result\" align=center>";
      print "0";
      print "</td>";
    //}
    $filledarray2 = getfilledvolunteers($grpelement, $groupdisplay, $filledcombinedgroups);
    print "<td class=\"result\" align=center>";
    foreach ($filledarray2 as $volunteername) {
      print $volunteername;
      print "<br>";
    }
    print "</td>";

    print "</tr>";
	  }
    
  }

  /* this function returns volunteers signed up for the days/times where there are open slots.*/
  function getfilledvolunteers($grpelement, $groupdisplay, $filledarray)
  {
    $openavailabledate = $grpelement->mystartdate;
    $openavailablestarttime = $grpelement->mystarttime;
    $openavailableitem = $grpelement->myitem;
    $filledarray2 = array();

    foreach ($filledarray as $value) {
      if ($value->signupgroupname == $groupdisplay) {
        $abc = $value->grouparray;
        foreach ($abc as $dtfilled) {
          $offset = $dtfilled->offset;
          $offset1 = substr($offset, 4, -3);
          $startdate = strtotime($dtfilled->startdatestring);
          $startdatearray = getdate($startdate);
          $formattedstartdate = date('l, m/d/y', $startdate);
          $formattedstarttime = getformattedtime($offset1, $startdatearray);
          $signedupvolunteername = $dtfilled->firstname . " " . $dtfilled->lastname;
          if (str_contains($groupdisplay, 'volunteer')) {
            if ($formattedstartdate == $openavailabledate && $formattedstarttime == $openavailablestarttime) {
              /* there is an issue where multiple sign up lists contain some of the same volunteers (ex: liz desmit present 
              on lists with sign up ids 49290481 and 47940394 for the exactly same date and time slot.), so 
              the following logic is needed to dedup volunteers that are shown twice. */
              if (!in_array($signedupvolunteername, $filledarray2)) {
                array_push($filledarray2, $signedupvolunteername);
              }
            }
          } else {
            if ($formattedstartdate == $openavailabledate && $dtfilled->item == $openavailableitem) {
              if (!in_array($signedupvolunteername, $filledarray2)) {
                array_push($filledarray2, $signedupvolunteername);
              }
            }
          }
        }
      }
    }

    return $filledarray2;
  }

  /* this function has logic to build "neighboring" slots - meaning - slots that
  are on the same day as there are open volunteer slots. for those*/
  function getneighboringslots($filteredsignupgroup, $filledarray) {
    $mynewarray = $filteredsignupgroup->grouparray;
    $mygroupname = $filteredsignupgroup->signupgroupname;
    $neighborarray = array();
    $newfilledarray = array();
    $datesarray = array();
    $datetimesarray = array();

    /* form an array of dates/times and array of dates of open volunteer slots.
    the times are being kept track because there is no need to build records for those time slots
    that are open volunteer slots. the array of dates are to keep track of days where there are empty volunteer slots.
    basically - we are building "neighboring" slots to those slots where there are volunteer openings. */
    foreach($mynewarray as $filtitem) {
      $startdatestring = $filtitem->mystartdatestring;
      $startdate = strtotime($startdatestring);
        $startdatearray = getdate($startdate);
        $formattedstartdate = date('m-d-y', $startdate);
      if (!in_array($startdatestring, $datetimesarray))  {
        array_push($datetimesarray, $startdatestring);
        array_push($datesarray, $formattedstartdate);
      }
    }

    /* keep only array of group of interest.*/
    foreach ($filledarray as $filledelement) {
      if ((str_contains($mygroupname, 'volunteers')) && $filledelement->signupgroupname == $mygroupname) {
        $newfilledarray = $filledelement->grouparray;
      }
  }
  
      foreach ($newfilledarray as $filleditem) {
        $filledelementstartdate = $filleditem->startdatestring;
        $startdate = strtotime($filledelementstartdate);
        $startdatearray = getdate($startdate);
        $weekday = date('l', $startdate);
        $offset = $filleditem->offset;
        $offset1 = substr($offset, 4, -3);
        $enddatestring = $filleditem->enddatestring;
        $enddate = strtotime($enddatestring);
        $enddatearray = getdate($enddate);
        $comparisonstartdate = date('m-d-y', $startdate);
        $formattedstartdate = date('l, m/d/y', $startdate);
        $formattedstarttime = getformattedtime($offset1, $startdatearray);
        $formattedendtime = getformattedtime($offset1, $enddatearray);
        if (in_array($comparisonstartdate, $datesarray) && !in_array($filledelementstartdate, $datetimesarray)) {
          /* this class is fit for building a "slot". note that open slots is set to 0 because there are no empty slots for those
          records. */
          $neighborslot = new signupitem($filleditem->signupid, $filleditem->item, $weekday, $filleditem->startdatestring, 
          $formattedstartdate, $formattedstarttime, $formattedendtime, 0);
          array_push($neighborarray, $neighborslot);
        }
      }
      $filtered = array_intersect_key($neighborarray, array_unique(array_column($neighborarray, 'mystartdatestring')));

      return $filtered;
    }

  function getnextfivebusinessdaystoandfrom()
  {
    $dates = array();
    $date = new datetime();

    while (count($dates) < 5) {
      $date->add(new dateinterval('p1d'));
      if ($date->format('n') < 6)
        $dates[] = $date->format('y-m-d');
    }

    return new formdates(min($dates), max($dates));
  }


  ?>

</body>

</html>
