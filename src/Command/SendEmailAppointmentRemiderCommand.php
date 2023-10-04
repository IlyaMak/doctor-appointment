<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\ScheduleSlotRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:send-email-appoinment-reminder')]
class SendEmailAppointmentRemiderCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private ScheduleSlotRepository $scheduleSlotRepository,
        #[Autowire(env: 'EMAIL_ADDRESS')]
        private string $emailAddress,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scheduleSlots = $this->scheduleSlotRepository->getPaidTomorrowScheduleSlots();
        if (count($scheduleSlots) === 0) {
            $output->writeln('Not found available paid slots.');
            return Command::FAILURE;
        }

        foreach ($scheduleSlots as $scheduleSlot) {
            /** @var User */
            $patient = $scheduleSlot->getPatient();
            $email = (new Email())
                ->from($this->emailAddress)
                ->to($patient->getEmail())
                ->subject(
                    '🔔 Reminder: you have a doctor appointment tomorrow'
                )
                ->text(
                    'Hello! You have a doctor appointment ' . $scheduleSlot->getStart()->format('Y-m-d H:s') . '. If you want to cancel the appointment reply to this email with the text "Cancel".'
                )
            ;
            $this->mailer->send($email);
        }
        $output->writeln(count($scheduleSlots) . ' mail(s) sent');
        return Command::SUCCESS;
    }
}
