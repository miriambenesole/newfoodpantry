
<?php
class MySignUpClass {
  public $myitem;
  public $myweekday;
  public $mystartdate;
  public $mystarttime;
  public $myendtime;
  public $myopenslots;
  public $myvolunteers;

  public function __construct(string $b, string $c, string $e, string $f, string $g, int $h, array $i) {
    $this->myitem = $b;
    $this->myweekday = $c;
    $this->mystartdate = $e;
    $this->mystarttime = $f;
    $this->myendtime = $g;
    $this->myopenslots = $h;
    $this->myvolunteers = $i;
}

static function cmp_obj($a,$b) {
    return strtolower($a->mystartdate) <=> strtolower($b->mystartdate);
}
}
