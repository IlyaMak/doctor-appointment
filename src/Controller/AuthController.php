<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ScheduleSlotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Exception;

#[Route('/{_locale<%app.supported_locales%>}')]
class AuthController extends AbstractController
{
    #[Route('/sign-in', name: 'app_sign_in')]
    public function app_sign_in(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/sign_in.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/sign-out', name: 'app_sign_out')]
    public function app_sign_out(): Response
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/login-success', name: 'login_success')]
    public function successLogin(
        TokenInterface $token,
        ScheduleSlotRepository $scheduleSlotRepository
    ): Response {
        /** @var User */
        $user = $token->getUser();
        if (in_array(User::ROLE_PATIENT, $user->getRoles())) {
            if (count($scheduleSlotRepository->getBookedScheduleSlotsByPatient($user)) === 0) {
                return $this->redirectToRoute('patient_book_an_appointment');
            } else {
                return $this->redirectToRoute('patient_appointment_history');
            }
        }

        return $this->redirectToRoute('schedule');
    }
}
