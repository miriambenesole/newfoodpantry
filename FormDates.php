<?php
class FormDates {
  public $fromDate;
  public $toDate;

  public function __construct(string $b, string $c) {
    $this->fromDate = $b;
    $this->toDate = $c;
}
}