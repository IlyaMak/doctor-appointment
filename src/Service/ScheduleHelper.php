<?php

namespace App\Service;

class ScheduleHelper
{
    /** @return int[] */
    public static function getAvailableIntHours(): array
    {
        return range(8, 20);
    }

    /** @return string[] */
    public static function getAvailableTimeHours(): array
    {
        $hours = [];
        $startTime = new \DateTime('07:00');
        foreach (ScheduleHelper::getAvailableIntHours() as $hour) {
            $hours[] = $startTime->add(new \DateInterval('PT1H'))->format('H:i');
        }

        return $hours;
    }
}
