<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CustomAbstractController extends AbstractController
{
    public function getUserCustom(): ?User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \Exception(sprintf('Expected App\\Entity\\User, got %s', null === $user ? 'null' : get_class($user)));
        }

        return $user;
    }
}
