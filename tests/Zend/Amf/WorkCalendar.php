<?php
class WorkCalendar
{
    public $id;
    public $name;
    /**
     * @var array $periods The time periods
     * @serialize Vector<net.fproject.calendar.Period>
     * */
    public $periods;

    /**
     * @var array $periods The time periods
     * @amftype Vector<net.fproject.calendar.WeekDay>
     * */
    public $weekDays;

    public $baseCalendar;
}