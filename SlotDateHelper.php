
<?php
class SlotDateHelper {
  public $weekday;
  public $startdate;
  public $starttime;
  public $endtime;
  public $item;

  public function __construct(string $a, string $b, string $c, string $d, string $e) {
    $this->weekday = $a;
    $this->startdate = $b;
    $this->starttime = $c;
    $this->endtime = $d;
    $this->item = $e;
}

static function cmp_obj($a,$b) {
    return strtolower($a->mystartdate) <=> strtolower($b->mystartdate);
}
}
