<?php

namespace App\Service;

use App\Entity\ScheduleSlot;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

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

    /** @return array<string, bool|string>[] */
    public static function getWeek(DateTimeImmutable $requestedDay): array
    {
        $dateTime = $requestedDay->modify('last sunday');
        $week = [];
        $currentDate = new DateTimeImmutable();

        foreach (range(0, 6) as $day) {
            $dateTime = $dateTime->add(new DateInterval('P1D'));
            $week[] = [
                'dayOfTheWeek' => $dateTime->format('D'),
                'dayOfTheMonth' => $dateTime->format('j'),
                'isHighlighted' => $dateTime->format('Y-m-d') === $currentDate->format('Y-m-d'),
            ];
        }

        return $week;
    }

    /** @param string[] $availableHours
     *  @return array<string, array<string, array<string, int|string>[]>>
     */
    private static function getEmptyWeekSchedule(DateTimeImmutable $requestedDay, array $availableHours): array
    {
        $schedule = [];
        foreach ($availableHours as $hour) {
            $dayOfTheWeek = $requestedDay->modify('monday this week');
            $schedule[$hour] = [];

            foreach (range(0, 6) as $day) {
                $schedule[$hour][$dayOfTheWeek->format('Y-m-d')] = [];
                $dayOfTheWeek = $dayOfTheWeek->add(new DateInterval('P1D'));
            }
        }

        return $schedule;
    }

    /** @param string[] $availableHours
     *  @param ScheduleSlot[] $scheduleSlots
     *  @return array<string, array<string, array<string, int|string>[]>>
    */
    public static function getWeekSchedule(
        DateTimeImmutable $requestedDay,
        array $availableHours,
        array $scheduleSlots,
    ): array {
        $schedule = CalendarHelper::getEmptyWeekSchedule($requestedDay, $availableHours);

        foreach ($scheduleSlots as $scheduleSlot) {
            $hour = $scheduleSlot->getStart()->format('H:00');
            $date = $scheduleSlot->getStart()->format('Y-m-d');
            $schedule[$hour][$date][] = [
                'id' => (int) $scheduleSlot->getId(),
                'startMinutes' => (int) $scheduleSlot->getStart()->format('i'),
                'duration' => (int) (($scheduleSlot->getEnd()->getTimestamp() - $scheduleSlot->getStart()->getTimestamp()) / 60),
                'timeTitle' => $scheduleSlot->getStart()->format('H:i') . ' -' . $scheduleSlot->getEnd()->format('H:i'),
            ];
        }

        return $schedule;
    }

    public static function getMondayOfTheRequestedDate(Request $request): DateTimeImmutable
    {
        if (
            $request->query->get('date') === null
            || ($requestedDay = DateTimeImmutable::createFromFormat('Y-m-d', (string) $request->query->get('date'))) === false
            || ($requestedDay < DateTimeImmutable::createFromFormat('Y-m-d', '2023-01-01'))
        ) {
            return new DateTimeImmutable('monday this week');
        }
        return $requestedDay->modify('monday this week');
    }
}
