<?php

namespace App\Model;

use DateTime;

class ScheduleSlotModel
{
    /** @param array<string, ?string> $excludedDaysOfTheWeek */
    public function __construct(
        public readonly DateTime $startDate,
        public readonly DateTime $endDate,
        public readonly DateTime $startTime,
        public readonly DateTime $endTime,
        public readonly string $patientServiceInterval,
        public readonly DateTime $startLunchTime,
        public readonly DateTime $endLunchTime,
        public readonly array $excludedDaysOfTheWeek,
        public readonly float $price
    ) {
    }
}
