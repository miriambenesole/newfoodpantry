<?php
class EmptySlot
{
    public $startdate;
    public $starttime;
    public $endtime;
    public $emptySlotsCount;

    public function __construct(string $a, string $b, string $c, int $d)
    {
        $this->startdate = $a;
        $this->starttime = $b;
        $this->endtime = $c;
        $this->emptySlotsCount = $d;
    }

    // This comparison function facilitates the re-ordering by date.
    static function cmp_obj($a, $b)
    {
        return strtolower($a->startdate) <=> strtolower($b->startdate);
    }
}
