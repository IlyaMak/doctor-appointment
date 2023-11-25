<?php

namespace App\Tests\Unit\Service;

use App\Entity\ScheduleSlot;
use App\Entity\User;
use App\Model\ScheduleSlotModel;
use App\Repository\ScheduleSlotRepository;
use App\Service\ScheduleSlotService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ScheduleSlotServiceTest extends TestCase
{
    private ScheduleSlotService $scheduleSlotService;
    private EntityManagerInterface $entityManager;
    private ScheduleSlotRepository $scheduleSlotRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->scheduleSlotRepository = $this->createMock(ScheduleSlotRepository::class);
        $this->scheduleSlotService = new ScheduleSlotService(
            $this->entityManager,
            $this->scheduleSlotRepository
        );
    }

    public function testScheduleSlotsGenerationContent(): void
    {
        $scheduleSlotModel = new ScheduleSlotModel(
            DateTime::createFromFormat('Y-m-d', '2023-11-20'),
            DateTime::createFromFormat('Y-m-d', '2023-12-11'),
            DateTime::createFromFormat('H:i', '08:00'),
            DateTime::createFromFormat('H:i', '08:30'),
            '30',
            DateTime::createFromFormat('H:i', '12:00'),
            DateTime::createFromFormat('H:i', '13:00'),
            [
                'monday' => null,
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'sunday' => 'Sunday',
                'saturday' => 'Saturday'
            ],
            5.00
        );

        self::assertEquals(
            $this->scheduleSlotService->generateScheduleSlots($scheduleSlotModel, new User()),
            [
                new ScheduleSlot(
                    DateTime::createFromFormat('Y-m-d H:i', '2023-11-20 08:00'),
                    DateTime::createFromFormat('Y-m-d H:i', '2023-11-20 08:30'),
                    5.00,
                    new User()
                ),
                new ScheduleSlot(
                    DateTime::createFromFormat('Y-m-d H:i', '2023-11-27 08:00'),
                    DateTime::createFromFormat('Y-m-d H:i', '2023-11-27 08:30'),
                    5.00,
                    new User()
                ),
                new ScheduleSlot(
                    DateTime::createFromFormat('Y-m-d H:i', '2023-12-04 08:00'),
                    DateTime::createFromFormat('Y-m-d H:i', '2023-12-04 08:30'),
                    5.00,
                    new User()
                ),
                new ScheduleSlot(
                    DateTime::createFromFormat('Y-m-d H:i', '2023-12-11 08:00'),
                    DateTime::createFromFormat('Y-m-d H:i', '2023-12-11 08:30'),
                    5.00,
                    new User()
                )
            ],
        );
    }

    public function testScheduleSlotsGenerationCount(): void
    {
        $scheduleSlotModel = new ScheduleSlotModel(
            DateTime::createFromFormat('Y-m-d', '2024-01-01'),
            DateTime::createFromFormat('Y-m-d', '2024-12-31'),
            DateTime::createFromFormat('H:i', '08:00'),
            DateTime::createFromFormat('H:i', '08:30'),
            '30',
            DateTime::createFromFormat('H:i', '12:00'),
            DateTime::createFromFormat('H:i', '13:00'),
            [
                'monday' => null,
                'tuesday' => null,
                'wednesday' => null,
                'thursday' => null,
                'friday' => null,
                'sunday' => null,
                'saturday' => null
            ],
            5.00
        );

        self::assertCount(
            366,
            $this->scheduleSlotService->generateScheduleSlots($scheduleSlotModel, new User()),
        );
    }
}
