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


<table>
  <td>
  <tr>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
      <b><label class="selecttext" for="group">1) Select a volunteer group:</label></b>
      <select name="group" id="group">
        <option value="">------ ------</option>
        <option value="Food Pantry Volunteers">Food Pantry Volunteers</option>
        <option value="Food Pantry Couriers">Food Pantry Couriers</option>
        <option value="Clothes Closet Volunteers">Clothes Closet Volunteers</option>
        <option value="Clothes Closet Couriers">Clothes Closet Couriers</option>
        <option value="all">ALL</option>
      </select>

      <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
        <b><input type="submit" id="next5days" name="next5days" class="submit" value="Next 5 business days"></b>
  </tr>
  <p class="selectdates"><b>or</b> Select dates</p>
  <tr>
    <b><label for="startdate">Start Date:</label></b>
    <input type="date" class="formdates" id="startdate" name="startdate" value="" min="<?php echo date("Y-m-d"); ?>">
    <label class="padding" for="enddate">End Date:</label>
    <input type="date" class="formdates" id="enddate" name="enddate" value="" min="<?php echo date("Y-m-d"); ?>">
    <input type="submit" name="submit" id="submitclear" class="submit" value="Submit/Clear">
  </tr>
  </form>
  </br>
  </td>
  <tr>
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
        <option value="Clothes Closet Volunteers">Clothes Closet Volunteers</option>
        <option value="Clothes Closet Couriers">Clothes Closet Couriers</option>
      </select>
      <input type="submit" name="searchname" id="searchname" class="submit" value="Search/Clear">
      </div>
    </form>
    </div>
  </tr>
</br>

  <!-- View upcoming Training -->
  <!-- <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <label class="foodtraining">3) Upcoming Food Pantry Training:</label>

    <input type="submit" id="submittraining" name="submittraining" class="submit" value="View">
  </form>
  </br> -->
  </table>

  <?php
  require ('SignUpGroupClass.php');
  require ('SignUpItem.php');
  require ('SignUpGroupSearchClass.php');
  require ('TrackerSignUpGroup.php');
  require ('FormDates.php');
  require ('PersonSignUp.php');
  include ('trainingsignups.php');

  global $signupidsearcharray2;

  if (isset($_POST['submit'])) {
    $formStartDate = $_POST['startdate'];
    $formEndDate = $_POST['enddate'];
    $group = $_POST['group'];
  }

  if (isset($_POST['searchname'])) {
    $name = $_POST['name'];
    $group1 = $_POST['group1'];
    searchVolunteerSignUps($name, $group1);
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
    // print "PLEASE SELECT A GROUP";
  } else {
    $data = getSignUpIDs();

    $myArray = getSignUpData($data);

    $selectedGroup = "";
    $group_result_start_date = "";
    $group_result_end_date = "";
    $signupidsearcharray = array();

    /* The following is filtering logic for obtaining sign up ids only for groups of interest. */

    foreach ($myArray as $mygroup) {
      switch ($group) {
        case "Food Pantry Volunteers":
          if (str_contains($mygroup->gptitle, 'Food Pantry') && !str_contains($mygroup->gptitle, 'Training')) {
            $a = $mygroup->gpsignupid;
            $b = $mygroup->gptitle;
            $c = $mygroup->gpstartdatestring;
            $d = $mygroup->gpenddatestring;
            $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
            array_push($signupidsearcharray, $signupidsearchelement);
          }
          break;
        case "Food Pantry Couriers":
          if (str_contains($mygroup->gptitle, 'Grocery')) {
            $a = $mygroup->gpsignupid;
            $b = $mygroup->gptitle;
            $c = $mygroup->gpstartdatestring;
            $d = $mygroup->gpenddatestring;
            $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
            array_push($signupidsearcharray, $signupidsearchelement);
          }
          break;
        case "Clothes Closet Volunteers":
          if (str_contains($mygroup->gptitle, 'Clothes Closet') && !str_contains($mygroup->gptitle, 'Bin')) {
            $a = $mygroup->gpsignupid;
            $b = $mygroup->gptitle;
            $c = $mygroup->gpstartdatestring;
            $d = $mygroup->gpenddatestring;
            $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
            array_push($signupidsearcharray, $signupidsearchelement);
          }
          break;
        case "Clothes Closet Couriers":
          if (str_contains($mygroup->gptitle, 'Clothes Closet') && str_contains($mygroup->gptitle, 'Bin')) {
            $a = $mygroup->gpsignupid;
            $b = $mygroup->gptitle;
            $c = $mygroup->gpstartdatestring;
            $d = $mygroup->gpenddatestring;
            $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
            array_push($signupidsearcharray, $signupidsearchelement);
          }
          break;
        case "all":
          if (
            str_contains($mygroup->gptitle, 'Food Pantry') && !str_contains($mygroup->gptitle, 'Training') || str_contains($mygroup->gptitle, 'Grocery') ||
            str_contains($mygroup->gptitle, 'Clothes Closet')
          ) {
            $a = $mygroup->gpsignupid;
            $b = $mygroup->gptitle;
            $c = $mygroup->gpstartdatestring;
            $d = $mygroup->gpenddatestring;
            $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
            array_push($signupidsearcharray, $signupidsearchelement);
            break;
          }
      }
    }

    $signupidsearcharray2 = array();
    if (empty($signupidsearcharray)) {
    } else {
      /* We want to keep only signup IDs of interest and within the dates requested on the site form.
      we will collect more than one signup id for each category, in case the dates requested fall into 2 signups (end of the 2-month period). */
      $signupidsearcharray2 = categorizeSignUps($signupidsearcharray, $formStartDate);

      /* If no matches found for the desired timeframe, end the process here.*/
      if (empty($signupidsearcharray2)) {
        print "No Matches found";
        exit;
      }

      $filteredData = [];
      /* Get a list of only sign up ids. */
      if (!empty($signupidsearcharray2) || !is_null($signupidsearcharray)) {
        $signupsarray = array();
        foreach ($signupidsearcharray2 as $signupidsearch2) {
          $signupidforsearch = $signupidsearch2->searchSignUpID;
          array_push($signupsarray, $signupidforsearch);
        }

        $parallelOpenSignUps = array();
        $parallelOpenSignUps = getParallelVolunteerSlots("available", $signupsarray);
        $filledSignUps = getParallelVolunteerSlots("filled", $signupsarray);
        $openNamedGroups = array();
        $resultarray = array();

        /* $openNamedGroups will correlate the group name with the array of open slots. */
        $openNamedGroups = getNamedGroups($parallelOpenSignUps, $signupidsearcharray2);
        /* $filledNamedGroups will correlate the group name with the array of filled slots. */
        $filledNamedGroups = getNamedGroups($filledSignUps, $signupidsearcharray2);

        $openCombinedGroups = getCombinedGroups($openNamedGroups);
        /* At this point, $openCombinedGroups has an array of 4 TrackerSignUpGroups that have open slots. */

        $filledCombinedGroups = getCombinedGroups($filledNamedGroups);
        /* At this point, $filledCombinedGroups has an array of 4 TrackerSignUpGroups that have filled slots. */

        $keepTrackOfAllOption = array();

        /* Now that we have them sorted and combined, we can use common logic to print them */
        foreach ($openCombinedGroups as $namedGroup) {
          /* $namedGroup group name (ex: "Food Pantry Volunteers"): */
          $groupName = $namedGroup->signUpGroupName;
          /* $namedGroup's array of sign ups: */
          $groupResult = $namedGroup->groupArray;
          $signUpIDGroup = array();
          $filteredDataInd = array();
          /* Proceeding with the logic to formate dates appropriately: */
          foreach ($groupResult as $result) {
            $mysignupid = $result->signupid;
            $item = $result->item;
            $offset = $result->offset;
            $offset1 = substr($offset, 4, -3);
            $startdate = strtotime($result->startdatestring);
            $startdatearray = getDate($startdate);
            $formattedStartDate = date('l, m/d/Y', $startdate);
            $enddatestring = $result->enddatestring;
            $enddate = strtotime($enddatestring);
            $enddatearray = getdate($enddate);
            $formattedStartTime = getFormattedTime($offset1, $startdatearray);

            $formattedEndTime = getFormattedTime($offset1, $enddatearray);

            $weekday = $startdatearray['weekday'];
            /* SignUpItem is an object that holds only the fields of interest for each sign up.*/
            $obj3 = new SignUpItem($mysignupid, $item, $weekday, $result->startdatestring, $formattedStartDate, $formattedStartTime, $formattedEndTime, $result->myqty);
            array_push($signUpIDGroup, $obj3);
          }

          /* The following logic filters sign ups to only those that fall within the timeframe requested. */
          foreach ($signUpIDGroup as $signupitem) {
            /* For each item, get the Start Date in the desired format for comparison with the form's start and end dates. */
            $abc = strtotime($signupitem->mystartdate);
            $abc = date('Y-m-d', $abc);
            if ($abc >= $formStartDate && $abc <= $formEndDate) {
              array_push($filteredDataInd, $signupitem);
            }
          }
          $filteredSignUpGroup = new TrackerSignUpGroup($groupName, $filteredDataInd);
          /* $keepTrackOfAllOption will keep track of which the filtered data belongs to (for "all" option). */
          array_push($keepTrackOfAllOption, $filteredSignUpGroup);

          $myGroupName = $filteredSignUpGroup->signUpGroupName;
          $myArray = $filteredSignUpGroup->groupArray;

          /* Add other filled slots on same day. */
          $neighborArray = getNeighboringSlots($filteredSignUpGroup, $filledCombinedGroups);

          $array3 = array();
          foreach ($neighborArray as $neighbor) {
            array_push($array3, $neighbor);
          }
          foreach($myArray as $itemArray) {
            array_push($array3, $itemArray);
          }

          if (!empty($array3)) {
            print "<table margin=auto border=1 cellpadding=5px>";
            /* Sign ups from potentially multiple lists are mixed (in terms of dates). We will now sort them and print them. */
            usort($array3, [SignUpItem::class, "cmp_obj"]);
            /* Get appropriate headings. */
            getPrintedHeadings($myGroupName);
            /* For each group we will be matching up data from the open slots results with the filled slots results
            that correspond to those days where there are open slots. */
            foreach ($array3 as $grpElement) {
              getGroupPrinted($grpElement, $myGroupName, $filledNamedGroups);
            }
            print "</table>";
          } else {
            /* If no results remained for  a given group that was requested, display message specific to the group requested. */
            if ($myGroupName == $group) {
              if ($formStartDate == $formEndDate) {
                print "<p class=\"noresults\">";
                print "No open volunteer slots " . " on " . $formStartDate . " for " . $groupName . ".";
                print "</p>";
              } else {
                print "<p class=\"noresults\">";
                print "No open volunteer slots " . " during " . $formStartDate . " and " . $formEndDate . " for " . $groupName . ".";
                print "</p>";
              }
            }
          }
        }

        /* If user selected "all" option, we need to find out if results existed for all 4 categories. If not - 
        proper message needs to be displayed. This is necessary because after filtering sign ups, there is a chance 
        that a given group does not have any open volunteer slots results.*/
        $j = 0;
        $i = 0;
        if ($group == "all") {
          for ($i == 0; $i <= 3; $i++) {
            /* Keep a counter of how many groups are empty. */
            if (empty($keepTrackOfAllOption[$i]->groupArray)) {
              $j++;
            }
          }
          if ($j == 4) {
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
        }
      }
    }
  }

  /* This function retrieves all signup IDs related to the API key. */
  function getSignUpIDs()
  {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.signupgenius.com/v2/k/signups/created/all/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $headers = array();
    $headers[] = "Accept: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $obj = json_decode($result, false);
    $data = $obj->data;

    return $data;
  }

  /* This function retrieves sign ups for an array of signupIDs. 
  Because each API call is fired in parallel - this is more  
  efficient than making one call at a time. All resuts are returned in an array. 
  This function can be used to call SignUpGenius open slots API and filled slots API*/
  function getParallelVolunteerSlots($option, $signupidsearcharray2)
  {
    $node_count = count($signupidsearcharray2);
    $master = curl_multi_init();

    for ($i = 0; $i < $node_count; $i++) {
      $signupid = $signupidsearcharray2[$i];
      $url = "https://api.signupgenius.com/v2/k/signups/report/" . $option . "/" . $signupid . "/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09";
      $curl_arr[$i] = curl_init($url);
      curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl_arr[$i], CURLOPT_CUSTOMREQUEST, "GET");

      $headers2 = array();
      $headers2[] = "Accept: application/json";
      curl_setopt($curl_arr[$i], CURLOPT_HTTPHEADER, $headers2);
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

  /* This function is used for extracting the desired datasets. */
  function getEachResult($result2)
  {
    $obj3 = json_decode($result2, false);
    if (property_exists($obj3, 'data')) {
      $data3 = $obj3->data;
      if (property_exists($data3, 'signup')) {
        return $data3->signup;
      } else {
        print "SignUp Genius API call failed.";
        exit;
      }
    } else {
      print "SignUp Genius API call failed.";
      exit;
    }
  }

  function getSignUpData($data) {
    $myArray = array();
    foreach ($data as $mydata) {
      $title = $mydata->title;
      $signupstartdatestring = $mydata->startdatestring;
      $signupenddatestring = $mydata->enddatestring;
      $signupid = $mydata->signupid;
      $obj = new SignUpGroupClass($signupid, $title, $signupstartdatestring, $signupenddatestring);
      array_push($myArray, $obj);
    }
    return $myArray;
  }

  function searchVolunteerSignUps($name, $group1) {
    $signupidsearcharray = array();
    $data = getSignUpIDs();
    $myArray = getSignUpData($data);

    foreach ($myArray as $mygroup) {
      if (
        str_contains($mygroup->gptitle, 'Food Pantry') && !str_contains($mygroup->gptitle, 'Training') || str_contains($mygroup->gptitle, 'Grocery') ||
        str_contains($mygroup->gptitle, 'Clothes Closet')
      ) {
        $a = $mygroup->gpsignupid;
        $b = $mygroup->gptitle;
        $c = $mygroup->gpstartdatestring;
        $d = $mygroup->gpenddatestring;
        $signupidsearchelement = new SignUpGroupClass($a, $b, $c, $d);
        array_push($signupidsearcharray, $signupidsearchelement);
      }
    }

    $todaydate = date('Y-m-d');
    $surroundingDates = getSurroundingBusinessDays();

    $signupidsearcharray2 = categorizeSignUps($signupidsearcharray, $todaydate);
    
    if (!empty($signupidsearcharray2) || !is_null($signupidsearcharray)) {
      $signupsarray = array();
      foreach ($signupidsearcharray2 as $signupidsearch2) {
        $signupidforsearch = $signupidsearch2->searchSignUpID;
        array_push($signupsarray, $signupidforsearch);
      }
      $filledSignUps = getParallelVolunteerSlots("filled", $signupsarray);

      $filledNamedGroups = getNamedGroups($filledSignUps, $signupidsearcharray2);

      $filledCombinedGroups = getCombinedGroups($filledNamedGroups);
    }
    $filteredDataInd = array();

    foreach($filledCombinedGroups as $combinedGroup) {
      if ($combinedGroup->signUpGroupName == $group1) {
        $groupArray = $combinedGroup->groupArray;
        $signUpIDGroup = array();
        foreach( $groupArray as $result) {
          $firstname = $result->firstname;
          $lastname = $result->lastname;
          $item = $result->item;
          $offset = $result->offset;
          $offset1 = substr($offset, 4, -3);
          $startdate = strtotime($result->startdatestring);
          $startdatearray = getDate($startdate);
          $formattedStartDate = date('l, m/d/Y', $startdate);
          $enddatestring = $result->enddatestring;
          $enddate = strtotime($enddatestring);
          $enddatearray = getdate($enddate);
          $formattedStartTime = getFormattedTime($offset1, $startdatearray);

          $formattedEndTime = getFormattedTime($offset1, $enddatearray);

          $weekday = $startdatearray['weekday'];
          /* SignUpItem is an object that holds only the fields of interest for each sign up.*/
          $obj3 = new PersonSignUp($firstname, $lastname, $weekday, $result->startdatestring, $formattedStartDate, $formattedStartTime, $formattedEndTime, $item);
          array_push($signUpIDGroup, $obj3);
        }
        foreach ($signUpIDGroup as $signupitem) {
          /* For each item, get the Start Date in the desired format for comparison with the form's start and end dates. */
          $abc = strtotime($signupitem->startdate);
          $abc = date('Y-m-d', $abc);
          if ($abc >= min($surroundingDates) && $abc <= max($surroundingDates)) {
            array_push($filteredDataInd, $signupitem);
          }
        }
      }
    }

    $personSignUpArray = array();
  
    foreach($filteredDataInd as $dataInd) {
      $fullName = $dataInd->firstname . " " . $dataInd->lastname;
      if($fullName == $name) {
        array_push($personSignUpArray, $dataInd);
      }
    }
    usort($personSignUpArray, [PersonSignUp::class, "cmp_obj"]);

    if(!empty($personSignUpArray)) {
    printVolunteer($personSignUpArray, $group1, $name);
    } else {
      print "<p class=\"nosignups\">";
      print "No sign ups for " . $name . " between " . min($surroundingDates) . " and " . max($surroundingDates) . ".";
      print "</p>";
    }
  }

  function printVolunteer($personSignUpArray, $searchGroup, $name) {
    print "<table margin=auto border=1 cellpadding=5px>";
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

/* This function takes an array and organizes them into one of the 4 groups. It also filters for the dates of 
intrest. */
  function categorizeSignUps($signupidsearcharray, $formStartDate) {
    $signupidsearcharray2 = array();
    foreach ($signupidsearcharray as $signupidsearch) {
      $groupresultstartdate = strtotime($signupidsearch->gpstartdatestring);
      $groupresultstartdate = date('Y-m-d', $groupresultstartdate);
      $groupresultenddate = strtotime($signupidsearch->gpenddatestring);
      $groupresultenddate = date('Y-m-d', $groupresultenddate);
      $groupresultendtitle = $signupidsearch->gptitle;
      /* if group for search falls within the requested start date or greater. */
      if ($groupresultenddate >= $formStartDate) {
        /* Keep the signupid connected to the group of interest in a SignUpGroupSearchClass object, which connects each signup ID with the group of interest.  */
        if (str_contains($groupresultendtitle, 'Food Pantry') && !str_contains($groupresultendtitle, 'Training')) {
          $mysearch = new SignUpGroupSearchClass("Food Pantry Volunteers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'Grocery')) {
          $mysearch = new SignUpGroupSearchClass("Food Pantry Couriers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'Clothes Closet Bin')) {
          $mysearch = new SignUpGroupSearchClass("Clothes Closet Couriers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        } elseif (str_contains($groupresultendtitle, 'Clothes Closet')) {
          $mysearch = new SignUpGroupSearchClass("Clothes Closet Volunteers", $signupidsearch->gpsignupid);
          array_push($signupidsearcharray2, $mysearch);
        }
      }
    }

    return $signupidsearcharray2;
  }

  /* This function returns the correlation between group name and its array of results. */
  function getNamedGroups($parallelOpenSignUps, $signupidsearcharray2)
  {
    $namedGroups = array();

    foreach ($parallelOpenSignUps as $result2) {
      $resultarray = getEachResult($result2);
      $groupsignupid = $resultarray[0]->signupid;

      /* Keep track of the 4 categories 
      signupidsearcharray2 kept track of each sign up id to group */
      foreach ($signupidsearcharray2 as $signupidsearch) {
        if ($signupidsearch->searchSignUpID == $groupsignupid) {
          $namedMatch = $signupidsearch->signUpGroup;
          /* TrackerSignUpGroup objects will correlate group name (ex: "Food Pantry Volunteers") to result array. */
          $namedSignupGroup = new TrackerSignUpGroup($namedMatch, $resultarray);
        }
      }
      array_push($namedGroups, $namedSignupGroup);
    }

    return $namedGroups;
  }

  function getCombinedGroups($namedGroups)
  {
    $myArray1 = array();
    $myArray2 = array();
    $myArray3 = array();
    $myArray4 = array();
    /* Several TrackerSignUpGroups were created, potentially for the same category group - so we need to put them together 
    Unfortunately, we first need to combine results of each category 
    This is where the logic to gather multiple sign up lists in order to process them starts. */
    foreach ($namedGroups as $groupElement) {
      if ($groupElement->signUpGroupName == "Food Pantry Volunteers") {
        array_push($myArray1, $groupElement->groupArray);
      }
      if ($groupElement->signUpGroupName == "Food Pantry Couriers") {
        array_push($myArray2, $groupElement->groupArray);
      }
      if ($groupElement->signUpGroupName == "Clothes Closet Volunteers") {
        array_push($myArray3, $groupElement->groupArray);
      }
      if ($groupElement->signUpGroupName == "Clothes Closet Couriers") {
        array_push($myArray4, $groupElement->groupArray);
      }
    }
    /* Now we need to have 1 TrackerSignUpGroup object for each category in order to print them.
    Once we have a single  TrackerSignUpGroup to an array of sign ups, we will be able to print them.  
    TrackerSignUpGroup object keeps track of group name to an array. */
    $combinedGroups = array();
    $namedSignupGroup1 = new TrackerSignUpGroup("Food Pantry Volunteers", combineArrays($myArray1));
    array_push($combinedGroups, $namedSignupGroup1);
    $namedSignupGroup3 = new TrackerSignUpGroup("Clothes Closet Volunteers", combineArrays($myArray3));
    array_push($combinedGroups, $namedSignupGroup3);
    $namedSignupGroup2 = new TrackerSignUpGroup("Food Pantry Couriers", combineArrays($myArray2));
    array_push($combinedGroups, $namedSignupGroup2);
    $namedSignupGroup4 = new TrackerSignUpGroup("Clothes Closet Couriers", combineArrays($myArray4));
    array_push($combinedGroups, $namedSignupGroup4);

    return $combinedGroups;
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

  function getFormattedTime($offset1, $datearray)
  {
    $starttime = $datearray['hours'];
    $starttime1 = $starttime - $offset1;
    $starttime2 = $starttime1 . ":" . $datearray['minutes'];
    $time = date_create($starttime2);
    $formattedStartTime = date_format($time, "h:ia");

    return $formattedStartTime;
  }

  function getPrintedHeadings($groupdisplay)
  {
	
    print "<th colspan=5 text-align=center><b>";
    print $groupdisplay;
    print "</b></th>";
    print "</p>";
    print "</h2>";
    print "<tr>";
    print "<th align=center><b>";
    print "Item";
    print "<th align=center><b>";
    print "Date";
    print "</b></th>";
    if (str_contains($groupdisplay, 'Volunteers')) {
      print "<th align=center><b>";
      print "Time Slot";
      print "</b></th>";
    }
    print "<th align=center><b>";
    print "Open Slots";
    print "</b></th>";
    print "<th align=center><b>";
    print "Volunteers signed up";
    print "</b></th>";
    print "</tr>";
  }


  function getGroupPrinted($grpElement, $groupdisplay, $filledCombinedGroups)
  {
    print "<tr>";
    print "<td class=\"result\" id=\"result\"  align=center>";
    print $grpElement->myitem;
    print "</td>";
    print "<td class=\"result\" align=center>";
    print $grpElement->mystartdate;
    print "</td>";
    if (str_contains($groupdisplay, 'Volunteers')) {
      print "<td class=\"result\" align=center>";
      print $grpElement->mystarttime . " - " . $grpElement->myendtime;
      print "</td>";
    }
    if ($grpElement->myopenslots > 1) {
      print "<b>";
      print "<td class=\"bigresult\" align=center>";
      print $grpElement->myopenslots;
      print "</td>";
      print "</b>";
    } else {
      print "<td class=\"result\" align=center>";
      print $grpElement->myopenslots;
      print "</td>";
    }
    $filledArray2 = getFilledVolunteers($grpElement, $groupdisplay, $filledCombinedGroups);
    print "<td class=\"result\" align=center>";
    foreach ($filledArray2 as $volunteerName) {
      print $volunteerName;
      print "<br>";
    }
    print "</td>";

    print "</tr>";
  }

  /* This function returns volunteers signed up for the days/times where there are open slots.*/
  function getFilledVolunteers($grpElement, $groupdisplay, $filledArray)
  {
    $openAvailableDate = $grpElement->mystartdate;
    $openAvailableStartTime = $grpElement->mystarttime;
    $openAvailableItem = $grpElement->myitem;
    $filledArray2 = array();

    foreach ($filledArray as $value) {
      if ($value->signUpGroupName == $groupdisplay) {
        $abc = $value->groupArray;
        foreach ($abc as $dtFilled) {
          $offset = $dtFilled->offset;
          $offset1 = substr($offset, 4, -3);
          $startdate = strtotime($dtFilled->startdatestring);
          $startdatearray = getDate($startdate);
          $formattedStartDate = date('l, m/d/Y', $startdate);
          $formattedStartTime = getFormattedTime($offset1, $startdatearray);
          $signedUpVolunteerName = $dtFilled->firstname . " " . $dtFilled->lastname;
          if (str_contains($groupdisplay, 'Volunteer')) {
            if ($formattedStartDate == $openAvailableDate && $formattedStartTime == $openAvailableStartTime) {
              /* There is an issue where multiple sign up lists contain some of the same volunteers (ex: Liz DeSmit present 
              on lists with sign up ids 49290481 and 47940394 for the exactly same date and time slot.), so 
              the following logic is needed to dedup volunteers that are shown twice. */
              if (!in_array($signedUpVolunteerName, $filledArray2)) {
                array_push($filledArray2, $signedUpVolunteerName);
              }
            }
          } else {
            if ($formattedStartDate == $openAvailableDate && $dtFilled->item == $openAvailableItem) {
              if (!in_array($signedUpVolunteerName, $filledArray2)) {
                array_push($filledArray2, $signedUpVolunteerName);
              }
            }
          }
        }
      }
    }

    return $filledArray2;
  }

  /* This function has logic to build "neighboring" slots - meaning - slots that
  are on the same day as there are open volunteer slots. For those*/
  function getNeighboringSlots($filteredSignUpGroup, $filledArray) {
    $myNewArray = $filteredSignUpGroup->groupArray;
    $myGroupName = $filteredSignUpGroup->signUpGroupName;
    $neighborArray = array();
    $newFilledArray = array();
    $datesArray = array();
    $dateTimesArray = array();

    /* Form an array of dates/times and array of dates of open volunteer slots.
    The times are being kept track because there is no need to build records for those time slots
    that are open volunteer slots. The array of dates are to keep track of days where there are empty volunteer slots.
    Basically - we are building "neighboring" slots to those slots where there are volunteer openings. */
    foreach($myNewArray as $filtItem) {
      $startdatestring = $filtItem->mystartdatestring;
      $startdate = strtotime($startdatestring);
        $startdatearray = getDate($startdate);
        $formattedStartDate = date('m-d-Y', $startdate);
      if (!in_array($startdatestring, $dateTimesArray))  {
        array_push($dateTimesArray, $startdatestring);
        array_push($datesArray, $formattedStartDate);
      }
    }

    /* Keep only array of group of interest.*/
    foreach ($filledArray as $filledElement) {
      if ((str_contains($myGroupName, 'Volunteers')) && $filledElement->signUpGroupName == $myGroupName) {
        $newFilledArray = $filledElement->groupArray;
      }
  }
  
      foreach ($newFilledArray as $filledItem) {
        $filledElementStartdate = $filledItem->startdatestring;
        $startdate = strtotime($filledElementStartdate);
        $startdatearray = getDate($startdate);
        $weekday = date('l', $startdate);
        $offset = $filledItem->offset;
        $offset1 = substr($offset, 4, -3);
        $enddatestring = $filledItem->enddatestring;
        $enddate = strtotime($enddatestring);
        $enddatearray = getdate($enddate);
        $comparisonStartDate = date('m-d-Y', $startdate);
        $formattedStartDate = date('l, m/d/Y', $startdate);
        $formattedStartTime = getFormattedTime($offset1, $startdatearray);
        $formattedEndTime = getFormattedTime($offset1, $enddatearray);
        if (in_array($comparisonStartDate, $datesArray) && !in_array($filledElementStartdate, $dateTimesArray)) {
          /* this class is fit for building a "slot". Note that open slots is set to 0 because there are no empty slots for those
          records. */
          $neighborSlot = new SignUpItem($filledItem->signupid, $filledItem->item, $weekday, $filledItem->startdatestring, 
          $formattedStartDate, $formattedStartTime, $formattedEndTime, 0);
          array_push($neighborArray, $neighborSlot);
        }
      }
      $filtered = array_intersect_key($neighborArray, array_unique(array_column($neighborArray, 'mystartdatestring')));

      return $filtered;
    }

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
