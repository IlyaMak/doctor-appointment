<?php

namespace App\Service;

use DateInterval;
use DateTimeImmutable;

class CalendarHelper
{
    /** @return string */
    public static function getMonthYearTitle(DateTimeImmutable $requestedDay): string
    {
        $monday = $requestedDay->modify('monday this week');
        $sunday = $monday->modify('sunday this week');

        if ($monday->format('F') === $sunday->format('F')) {
            $monthYearTitle = $monday->format('F') . ' ' . $monday->format('Y');
        } elseif ($monday->format('Y') !== $sunday->format('Y')) {
            $monthYearTitle = $monday->format('M') . ' ' . $monday->format('Y') . ' - ' . $sunday->format('M') . ' ' . $sunday->format('Y');
        } else {
            $monthYearTitle = $monday->format('M') . ' - ' . $sunday->format('M') . ' ' . $sunday->format('Y');
        }

        return $monthYearTitle;
    }

    /** @return array<string, string>[] */
    public static function getWeek(DateTimeImmutable $requestedDay): array
    {
        $dateTime = $requestedDay->modify('last sunday');
        $week = [];

        foreach (range(0, 6) as $day) {
            $dateTime = $dateTime->add(new DateInterval('P1D'));
            $week[] = [
                'dayOfTheWeek' => $dateTime->format('D'),
                'dayOfTheMonth' => $dateTime->format('j'),
            ];
        }

        return $week;
    }
}
