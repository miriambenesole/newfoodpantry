<?php
class FilteredSignUpGroup {
  public $groupName;
  public $groupArray;

  public function __construct(string $b, array $c) {
    $this->groupName = $b;
    $this->groupArray = $c;
}
}