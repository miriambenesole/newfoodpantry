<?php
class SignUpGroupClass {
  public $gpsignupid;
  public $gptitle;

  public $gpstartdatestring;
  public $gpenddatestring;

  public function __construct(string $b, string $c, string $d, string $e) {
    $this->gpsignupid = $b;
    $this->gptitle = $c;
    $this->gpstartdatestring = $d;
    $this->gpenddatestring = $e;
}
}