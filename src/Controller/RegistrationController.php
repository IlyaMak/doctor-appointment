<?php

namespace App\Controller;

use App\Entity\Specialty;
use App\Entity\User;
use App\Form\DoctorRegistrationFormType;
use App\Form\PatientRegistrationFormType;
use App\Form\RegistrationTypeFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use DateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/{_locale<%app.supported_locales%>}')]
class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(
        EmailVerifier $emailVerifier,
        #[Autowire(param: 'is_required_email_verification')]
        private bool $isEmailVerificationRequired,
        private TranslatorInterface $translator,
    ) {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $authenticatorManager,
        #[Autowire(service: 'security.authenticator.form_login.main')]
        FormLoginAuthenticator $authenticator,
        #[Autowire(env: 'EMAIL_ADDRESS')]
        string $emailAddress,
    ): Response {

        $registrationTypeForm = $this->createForm(RegistrationTypeFormType::class);
        $registrationTypeForm->handleRequest($request);
        /** @var int */
        $type = $registrationTypeForm->get('type')->getData();
        $isDoctor = 0 === $type;
        $user = new User();
        $form = $this->createForm(
            $isDoctor
                ? DoctorRegistrationFormType::class
                : PatientRegistrationFormType::class,
            $user,
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string */
            $name = $form->get('name')->getData();
            /** @var string */
            $plainPassword = $form->get('plainPassword')->getData();
            /** @var string|Address */
            $email = $user->getEmail();
            $user->setName($name);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setLanguage($request->getLocale());

            if ($isDoctor) {
                /** @var ?Specialty */
                $specialty = $form->get('specialty')->getData();
                /** @var UploadedFile */
                $avatarData = $form->get('avatar')->getData();
                $user->setSpecialty($specialty);
                $currentDateTime = new DateTime();
                $currentDate = $currentDateTime->format('YmdHisv');
                $localAvatarPath = $currentDate . ' . ' . $avatarData->getClientOriginalExtension();
                $avatarData->move('resources', $localAvatarPath);
                $user->setAvatarPath($localAvatarPath);
                $user->setRoles([User::ROLE_DOCTOR]);
            } else {
                $user->setRoles([User::ROLE_PATIENT]);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            if ($this->isEmailVerificationRequired) {
                $this->emailVerifier->sendEmailConfirmation(
                    'app_verify_email',
                    $user,
                    (new TemplatedEmail())
                        ->from(new Address($emailAddress, $this->translator->trans('doctor_appointment_bot_name')))
                        ->to($email)
                        ->subject($this->translator->trans('email_confirmation_subject'))
                        ->htmlTemplate('security/confirmation_email.html.twig')
                        ->context([
                            'emailHeader' => $this->translator->trans('email_header'),
                            'emailDescription' => $this->translator->trans('confirmation_email_description'),
                            'emailLink' => $this->translator->trans('confirm_email_link'),
                            'expireLinkDescription' => $this->translator->trans('expire_link_description'),
                        ])
                );

                return $this->render('security/register_success.html.twig');
            } else {
                $authenticatorManager->authenticateUser($user, $authenticator, $request);
                if ($isDoctor) {
                    return $this->redirectToRoute('schedule');
                } else {
                    return $this->redirectToRoute('patient_book_an_appointment');
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
            'registrationTypeForm' => $registrationTypeForm->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository, LoggerInterface $logger): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            $logger->error('User id is not exists');

            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $logger->error('User is not exists');

            return $this->redirectToRoute('app_register');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', $this->translator->trans('verified_message_mark'));

        return $this->redirectToRoute('app_sign_in');
    }
}
