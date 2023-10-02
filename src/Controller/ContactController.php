<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function contact(
        Request $request,
        MailerInterface $mailer,
        #[Autowire(env: 'EMAIL_ADDRESS')]
        string $emailAddress,
    ): Response {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string */
            $topic = $form->get('topic')->getData();
            /** @var string */
            $userEmail = $form->get('email')->getData();
            /** @var string */
            $message = $form->get('message')->getData();
            $email = (new Email())
                ->from(new Address($userEmail, 'User ' . $userEmail))
                ->to($emailAddress)
                ->subject($topic)
                ->text($message)
            ;
            $mailer->send($email);
            $this->addFlash('success', 'The message was successfully sent!');

        }

        return $this->render('index/contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
