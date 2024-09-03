
<?php
class SignUpItem {
public $mysignupid;
  public $myitem;
  public $myweekday;
  public $mystartdatestring;
  public $mystartdate;
  public $mystarttime;
  public $myendtime;
  public $myopenslots;

  public function __construct(string $a,string $b, string $c, string $d, string $e, string $f, string $g, int $h) {
    $this->mysignupid = $a;
    $this->myitem = $b;
    $this->myweekday = $c;
    $this->mystartdatestring = $d;
    $this->mystartdate = $e;
    $this->mystarttime = $f;
    $this->myendtime = $g;
    $this->myopenslots = $h;
}
// This comparison function facilitates the re-ordering by date.
static function cmp_obj($a,$b) {
    return strtolower($a->mystartdatestring) <=> strtolower($b->mystartdatestring);
}
}