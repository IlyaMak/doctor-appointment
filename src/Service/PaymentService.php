<?php

namespace App\Service;

use App\Entity\ScheduleSlot;
use App\Entity\StripeTransaction;
use App\Enum\Status;
use App\Repository\ScheduleSlotRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PaymentService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ScheduleSlotRepository $scheduleSlotRepository,
        #[Autowire(env: 'STRIPE_SECRET')]
        private string $stripeSecret,
    ) {
    }

    public function getScheduleSlotPaymentLink(
        int $slotId,
        ScheduleSlot $scheduleSlot,
        string $successUrl,
        string $cancelUrl,
    ): string {
        $stripe = new StripeClient($this->stripeSecret);
        $checkoutSession = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'An appointment ' . $scheduleSlot->getStart()->format('Y-m-d H:i') . '-' . $scheduleSlot->getEnd()->format('H:i'),
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
                }
                break;
        }
        $this->entityManager->flush();
    }
}
