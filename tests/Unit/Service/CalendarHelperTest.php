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
                new DateTimeImmutable('2023-09-11')
            ),
            [
                [
                    'dayOfTheWeek' => 'Mon',
                    'dayOfTheMonth' => '11',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Tue',
                    'dayOfTheMonth' => '12',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Wed',
                    'dayOfTheMonth' => '13',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Thu',
                    'dayOfTheMonth' => '14',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Fri',
                    'dayOfTheMonth' => '15',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Sat',
                    'dayOfTheMonth' => '16',
                    'isHighlighted' => false,
                ],
                [
                    'dayOfTheWeek' => 'Sun',
                    'dayOfTheMonth' => '17',
                    'isHighlighted' => false,
                ],
            ]
        );
    }
}
