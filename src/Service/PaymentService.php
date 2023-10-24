<?php

namespace App\Service;

use App\Entity\ScheduleSlot;
use App\Entity\StripeTransaction;
use App\Entity\User;
use App\Enum\Status;
use App\Repository\ScheduleSlotRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Stripe\StripeClient;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ScheduleSlotRepository $scheduleSlotRepository,
        #[Autowire(env: 'STRIPE_SECRET')]
        private string $stripeSecret,
        private TranslatorInterface $translator,
        #[Autowire(env: 'EMAIL_ADDRESS')]
        private string $emailAddress,
        private MailerInterface $mailer,
    ) {
    }

    public function getScheduleSlotPaymentLink(
        int $slotId,
        ScheduleSlot $scheduleSlot,
        Request $request,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $stripe = new StripeClient($this->stripeSecret);
        $checkoutSession = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => $request->getLocale(),
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $this->translator->trans(
                            'product_data_message',
                            [
                                'startDate' => $scheduleSlot->getStart()->format('Y-m-d H:i'),
                                'endTime' => $scheduleSlot->getEnd()->format('H:i'),
                            ]
                        ),
                    ],
                    'unit_amount' => $scheduleSlot->getPrice() * 100,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'slotId' => $slotId,
            ]
        ]);

        /** @var string */
        $paymentUrl = $checkoutSession->url;
        return $paymentUrl;
    }

    public function handleStripeEvents(Event $event): void
    {
        switch ($event->type) {
            case 'payment_intent.payment_failed':
            case 'payment_intent.succeeded':
                /** @var array<string, int|string> */
                $paymentIntent = $event->data['object'];
                /** @var DateTimeImmutable */
                $paymentCreatedAt = DateTimeImmutable::createFromFormat('U', (string) $paymentIntent['created']);
                $stripeTransaction = new StripeTransaction(
                    (string) $paymentIntent['id'],
                    (int) $paymentIntent['amount'],
                    (string) $paymentIntent['currency'],
                    $paymentCreatedAt,
                );
                $this->entityManager->persist($stripeTransaction);
                break;
            case 'checkout.session.completed':
                /** @var array<string, array<string, string>> */
                $paymentIntent = $event->data['object'];
                $metaData = $paymentIntent['metadata'];
                $slotId = $metaData['slotId'];
                if ($slotId !== null) {
                    /** @var ScheduleSlot */
                    $scheduleSlot = $this->scheduleSlotRepository->find($slotId);
                    $scheduleSlot->setStatus(Status::Paid);
                    $doctorLanguage = $scheduleSlot->getDoctor()->getLanguage();
                    /** @var User */
                    $patient = $scheduleSlot->getPatient();

                    $email = (new TemplatedEmail())
                        ->from(
                            new Address(
                                $this->emailAddress,
                                $this->translator->trans(
                                    'doctor_appointment_bot_name',
                                    [],
                                    null,
                                    $doctorLanguage
                                )
                            )
                        )
                        ->to($scheduleSlot->getDoctor()->getEmail())
                        ->subject(
                            $this->translator->trans(
                                'email_paid_appointment_subject',
                                [],
                                null,
                                $doctorLanguage
                            )
                        )
                        ->text(
                            $this->translator->trans(
                                'email_paid_appointment_text',
                                [
                                    'patient' => $patient->getName(),
                                    'date' => $scheduleSlot->getStart()->format('Y-m-d'),
                                    'startTime' => $scheduleSlot->getStart()->format('H:i'),
                                    'endTime' => $scheduleSlot->getEnd()->format('H:i')
                                ],
                                null,
                                $doctorLanguage
                            )
                        )
                    ;

                    $this->mailer->send($email);
                }
                break;
        }
        $this->entityManager->flush();
    }
}
