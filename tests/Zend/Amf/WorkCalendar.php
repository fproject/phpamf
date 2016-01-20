<?php
class WorkCalendar
{
    public $id;
    public $name;
    /**
     * @var \fproject\calendar\Period[] $periods The time periods
     * @as3type Vector<net.fproject.calendar.Period>
     * */
    public $periods;

    /**
     * @var \fproject\calendar\WeekDay[] $periods The time periods
     * @as3type Vector<net.fproject.calendar.WeekDay>(fixed)
     * */
    public $weekDays;

    /**
     * @var \fproject\calendar\WorkShift[] $periods The time $defaultWorkShifts
     * @as3type Vector<net.fproject.calendar.WorkShift>
     */
    public $defaultWorkShifts;

    public $baseCalendar;
}