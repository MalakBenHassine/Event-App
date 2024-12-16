<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'isVerified') && !$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('Your account is not verified. Please check your email.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No post-authentication checks needed in this case
    }
}