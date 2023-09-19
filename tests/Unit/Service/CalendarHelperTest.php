<?php

namespace App\Tests\Unit\Service;

use App\Service\CalendarHelper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CalendarHelperTest extends TestCase
{
    public function testMonthYearTitle(): void
    {
        self::assertEquals(
            CalendarHelper::getMonthYearTitle(new DateTimeImmutable('2023-09-10')),
            'September 2023',
        );

        self::assertEquals(
            CalendarHelper::getMonthYearTitle(new DateTimeImmutable('2023-05-30')),
            'May - Jun 2023',
        );

        self::assertEquals(
            CalendarHelper::getMonthYearTitle(new DateTimeImmutable('2024-12-31')),
            'Dec 2024 - Jan 2025',
        );
    }

    public function testGetWeek(): void
    {
        self::assertEquals(
            CalendarHelper::getWeek(
                new DateTimeImmutable('2023-09-19')
            ),
            [
                [
                    'dayOfTheWeek' => 'Mon',
                    'dayOfTheMonth' => '18',
                ],
                [
                    'dayOfTheWeek' => 'Tue',
                    'dayOfTheMonth' => '19',
                ],
                [
                    'dayOfTheWeek' => 'Wed',
                    'dayOfTheMonth' => '20',
                ],
                [
                    'dayOfTheWeek' => 'Thu',
                    'dayOfTheMonth' => '21',
                ],
                [
                    'dayOfTheWeek' => 'Fri',
                    'dayOfTheMonth' => '22',
                ],
                [
                    'dayOfTheWeek' => 'Sat',
                    'dayOfTheMonth' => '23',
                ],
                [
                    'dayOfTheWeek' => 'Sun',
                    'dayOfTheMonth' => '24',
                ],
            ]
        );
    }
}
