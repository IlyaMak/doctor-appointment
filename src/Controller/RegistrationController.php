<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DoctorRegistrationFormType;
use App\Form\PatientRegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\RegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function __construct(
        #[Autowire(param: 'is_required_email_verification')]
        private bool $isEmailVerificationRequired,
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $entityManager,
        private UserAuthenticatorInterface $authenticatorManager,
        #[Autowire(service: 'security.authenticator.form_login.main')]
        private FormLoginAuthenticator $authenticator,
        private RegistrationService $registrationService
    ) {
    }

    #[Route('/patient-register', name: 'app_patient_register')]
    public function register(
        Request $request
    ): Response {
        $user = new User();
        $form = $this->createForm(PatientRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registrationService->setBasicUserProperties(
                $form,
                $user,
                $this->userPasswordHasher,
                $request
            );

            $user->setRoles([User::ROLE_PATIENT]);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if ($this->isEmailVerificationRequired) {
                $this->registrationService->sendEmailConfirmation($user);
                return $this->render('security/register_success.html.twig');
            } else {
                $this->authenticatorManager->authenticateUser($user, $this->authenticator, $request);
                return $this->redirectToRoute('patient_book_an_appointment');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/doctor-register', name: 'app_doctor_register')]
    public function doctorRegister(
        Request $request
    ): Response {
        $user = new User();
        $form = $this->createForm(DoctorRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->registrationService->setBasicUserProperties(
                $form,
                $user,
                $this->userPasswordHasher,
                $request
            );

            /** @var UploadedFile */
            $avatarData = $form->get('avatar')->getData();
            $currentDateTime = new DateTime();
            $currentDate = $currentDateTime->format('YmdHisv');
            $localAvatarPath = $currentDate . ' . ' . $avatarData->getClientOriginalExtension();
            $avatarData->move('resources', $localAvatarPath);
            $user->setAvatarPath($localAvatarPath);
            $user->setRoles([User::ROLE_DOCTOR]);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if ($this->isEmailVerificationRequired) {
                $this->registrationService->sendEmailConfirmation($user);
                return $this->render('security/register_success.html.twig');
            } else {
                $this->authenticatorManager->authenticateUser($user, $this->authenticator, $request);
                return $this->redirectToRoute('schedule');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository,
        LoggerInterface $logger,
        EmailVerifier $emailVerifier,
        TranslatorInterface $translator,
    ): Response {
        $id = $request->query->get('id');

        if (null === $id) {
            $logger->error('User ID cannot be null');

            return $this->redirectToRoute('app_patient_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            $logger->error("A user with ID $id does not exist");

            return $this->redirectToRoute('app_patient_register');
        }

        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_patient_register');
        }

        $this->addFlash('success', $translator->trans('verified_message_mark'));

        return $this->redirectToRoute('app_sign_in');
    }
}
