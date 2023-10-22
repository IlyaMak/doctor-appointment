<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\PaymentService;
use Psr\Log\LoggerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UnexpectedValueException;

class PaymentController extends CustomAbstractController
{
    #[Route('/api/payment/stripe', name: 'payment')]
    public function payment(
        Request $request,
        PaymentService $paymentService,
        LoggerInterface $logger,
        #[Autowire(env: 'STRIPE_ENDPOINT_SECRET')]
        string $stripeEndpointSecret,
    ): Response {
        /** @var string */
        $payload = $request->getContent();
        /** @var string */
        $sigHeader = $request->headers->get('Stripe-Signature');
        $event = null;

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $stripeEndpointSecret,
            );
        } catch (UnexpectedValueException $e) {
            $logger->error($e->getMessage());
            return new Response('', 400);
        } catch (SignatureVerificationException $e) {
            $logger->error($e->getMessage());
            return new Response('', 400);
        }

        $paymentService->handleStripeEvents($event);

        return new Response('', 200);
    }

    #[Route('/{_locale<%app.supported_locales%>}/api/payment/stripe/success', name: 'success_payment')]
    #[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
    public function successCheckout(): Response
    {
        return $this->render('payment/success.html.twig', []);
    }

    #[Route('/{_locale<%app.supported_locales%>}/api/payment/stripe/cancel', name: 'cancel_payment')]
    #[IsGranted(User::ROLE_PATIENT, message: 'You don\'t have permissions to access this resource')]
    public function cancelCheckout(): Response
    {
        return $this->render('payment/cancel.html.twig', []);
    }
}
