<?php

namespace App\Controller;

use App\Entity\Specialty;
use App\Entity\User;
use App\Form\DoctorRegistrationFormType;
use App\Form\PatientRegistrationFormType;
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

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(
        EmailVerifier $emailVerifier,
        #[Autowire(param: 'is_required_email_verification')]
        private bool $isEmailVerificationRequired,
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
    ): Response {
        $isDoctor = '0' === $request->query->get('isPatient');
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
                        ->from(new Address('ilmobdev@gmail.com', 'Doctor Appointment Mail Bot'))
                        ->to($email)
                        ->subject('Please Confirm your Email')
                        ->htmlTemplate('security/confirmation_email.html.twig')
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

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_sign_in');
    }
}
