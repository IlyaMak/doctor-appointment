<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        #[Autowire(param: 'is_required_email_verification')]
        private bool $isEmailVerificationRequired,
    ) {
    }

    public function checkPreAuth(UserInterface $user)
    {
        if ($user instanceof User && !$user->isVerified() && $this->isEmailVerificationRequired) {
            throw new CustomUserMessageAuthenticationException('Your email is not verified!');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
