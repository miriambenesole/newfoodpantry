<?php
class TrackerSignUpGroup {
  public $signUpGroupName;
  public $groupArray;

  public function __construct(string $b, array $c) {
    $this->signUpGroupName = $b;
    $this->groupArray = $c;
}
}