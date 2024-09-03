<?php
class TrainingSignUp {
  public $myfirstname;
  public $mylastname;
  public $myweekday;
  public $mystartdatestring;
  public $mystartdate;
  public $myrawstartdate;
  public $mystarttime;
  public $myendtime;
  public $myemail;

  public function __construct(string $a,string $b, string $c, string $d, string $e, string $f, string $g, string $h, string $i) {
    $this->myfirstname = $a;
    $this->mylastname = $b;
    $this->myweekday = $c;
    $this->mystartdatestring = $d;
    $this->mystartdate = $e;
    $this->myrawstartdate = $f;
    $this->mystarttime = $g;
    $this->myendtime = $h;
    $this->myemail = $i;
 
}

static function cmp_obj($a,$b) {
    return strtolower($a->mystartdatestring) <=> strtolower($b->mystartdatestring);
}
}