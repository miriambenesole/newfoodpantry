<?php

require ('TrainingSignUp.php');

function getSignedUpSlots() {
    $ch3 = curl_init();
    curl_setopt($ch3, CURLOPT_URL, "https://api.signupgenius.com/v2/k/signups/report/filled/" . "49982877/?user_key=Ry84b1VJaEFDZUJvYWo4T2JVbExzdz09");
    curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch3, CURLOPT_CUSTOMREQUEST, "GET");

    $headers3 = array();
    $headers3[] = "Accept: application/json";
    curl_setopt($ch3, CURLOPT_HTTPHEADER, $headers3);

    $result3 = curl_exec($ch3);
    if (curl_errno($ch3)) {
      echo 'Error:' . curl_error($ch3);
    }
    curl_close($ch3);

    $obj3 = json_decode($result3, false);
    if (property_exists($obj3,'data')) {
        $data3 = $obj3->data;
        if (property_exists($data3,'signup')) {
            $signup = $data3->signup;
            if(empty($signup)) {
              print "No one signed up.";
            } else {
              getPeople($signup);
            }
        }
        else {
            print "SignUp Genius API call failed.";
        }
    }
    else {
        print "SignUp Genius API call failed.";
    }
  }

function getPeople($signup)
{
    $peoplearray = array();
    $array5 = array();
    foreach ($signup as $mysignup) {
        $offset = $mysignup->offset;
        $firstname = $mysignup->firstname;
        $lastname = $mysignup->lastname;
        $email = $mysignup->email;

        $offset1 = substr($offset, 4, -3);
        $startdate = strtotime($mysignup->startdatestring);
        $startdatearray = getDate($startdate);
        $formattedStartDate = date('l, m/d/Y', $startdate);
        $rawStartDate = date('m/d/Y', $startdate);

        $formattedStartTime = getFormattedStartTime($offset1, $startdatearray);
        $formattedEndTime = getFormattedEndTime($offset1, $mysignup);

        $weekday = $startdatearray['weekday'];

        $obj3 = new TrainingSignUp($firstname, $lastname, $weekday, $mysignup->startdatestring, $formattedStartDate, $rawStartDate, $formattedStartTime, $formattedEndTime, $email);

        array_push($peoplearray, $obj3);

        $futurepeople = array();
        foreach ($peoplearray as $personal) {
            if ($personal->myrawstartdate >= date("m/d/Y")) {
                array_push($futurepeople, $personal);
            }
        }

        $array5 = array();
        foreach ($futurepeople as $signupday) {
            $dailysignup = $signupday->myrawstartdate;
            // create list of dates
            array_push($array5, $dailysignup);
        }
        $arr = array_unique($array5);
    }

    $array40 = array();
    // take each date and see who are on that date.
    
    if ($arr) {
        foreach ($arr as $signupdate) {
            $ead = array();
            foreach ($peoplearray as $person) {
                if ($person->myrawstartdate == $signupdate) {
                    array_push($ead, $person);
                }
            }
            $abc = new SameDateSignUp($signupdate, $ead);
            array_push($array40, $abc);
            usort($array40, [SameDateSignUp::class, "cmp_obj"]);
        }
    
        print "<h3 class=\"training\">";
        print "The following volunteers are coming for training:";
        print "</h3>";
        print "<table margin=auto border=1 cellpadding=5px>";
        print "</p>";
        print "</h2>";
        print "<tr>";
        print "<th align=center><b>";
        print "Date";
        print "<th align=center><b>";
        print "Time";
        print "</b></th>";
        print "<th align=center><b>";
        print "Name";
        print "</b></th>";
        print "</tr>";
    
        foreach ($array40 as $signupday) {
            $dayofweek = date('l, m/d/Y', strtotime($signupday->signedUpDate));
            print "<tr>";
            print "<td align=center>";
            print $dayofweek;
            print "</td>";
            print "<td class=\"result\" id=\"result\"  align=center>";
            $gpPeople = $signupday->signedUpPeople;
            foreach ($gpPeople as $person) {
                print $person->mystarttime . " - " . $person->myendtime;
                print "<br>";
            }
            print "</td>";
            print "<td class=\"result\" id=\"result\"  align=center>";
            $gpPeople = $signupday->signedUpPeople;
            foreach ($gpPeople as $person) {
                print $person->myfirstname . " " . $person->mylastname . "<b>" . "   email: " . "</b>" . $person->myemail;
                print "<br>";
            }
            print "</td>";
            print "</tr>";
        }
        print "</table>";
    } else {
    print "No one signed up.";
    }

}

class SameDateSignUp
{
    public $signedUpDate;
    public $signedUpPeople = array();

    public function __construct(string $a, array $b)
    {
        $this->signedUpDate = $a;
        $this->signedUpPeople = $b;
    }
    static function cmp_obj($a, $b)
    {
        return strtolower($a->signedUpDate) <=> strtolower($b->signedUpDate);
    }
}

