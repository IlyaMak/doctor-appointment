<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationService
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private string $emailAddress,
        private TranslatorInterface $translator,
    ) {
    }

    public function setBasicUserProperties(
        FormInterface $form,
        User $user,
        UserPasswordHasherInterface $userPasswordHasher,
        Request $request
    ): void {
        /** @var string */
        $plainPassword = $form->get('plainPassword')->getData();
        $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
        $user->setLanguage($request->getLocale());
    }

    public function sendEmailConfirmation(
        User $user,
    ): void {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address($this->emailAddress, $this->translator->trans('doctor_appointment_bot_name')))
                ->to($user->getEmail())
                ->subject($this->translator->trans('email_confirmation_subject'))
                ->htmlTemplate('security/confirmation_email.html.twig')
                ->context([
                    'emailHeader' => $this->translator->trans('email_header'),
                    'emailDescription' => $this->translator->trans('confirmation_email_description'),
                    'emailLink' => $this->translator->trans('confirm_email_link'),
                    'expireLinkDescription' => $this->translator->trans('expire_link_description'),
                ])
        );
    }
}
