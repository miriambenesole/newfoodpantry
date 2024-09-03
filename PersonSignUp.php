<?php
class PersonSignUp
{
    public $firstname;
    public $lastname;
    public $weekday;
    public $startdate;
    public $starttime;
    public $endtime;
    public $item;

    public function __construct(string $a, string $b, string $c, string $e, string $f, string $g, string $h)
    {
        $this->firstname = $a;
        $this->lastname = $b;
        $this->weekday = $c;
        $this->startdate = $e;
        $this->starttime = $f;
        $this->endtime = $g;
        $this->item = $h;
    }

    // This comparison function facilitates the re-ordering by date.
    static function cmp_obj($a, $b)
    {
        return strtolower($a->startdate) <=> strtolower($b->startdate);
    }
}
