<?php

namespace App\Tests;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use App\Entity\PasswordResetToken;
use App\Entity\Events;
use App\Entity\Reservation;


class UnitTestUserTest extends TestCase
{
    public function testUserAttributes(): void
    {
        $user = new User();
        $user->setName('John Doe')
            ->setPhone(123456789)
            ->setEmail('johndoe@example.com')
            ->setPassword('password123')
            ->setVerified(true);

        $this->assertEquals('John Doe', $user->getName());
        $this->assertEquals(123456789, $user->getPhone());
        $this->assertEquals('johndoe@example.com', $user->getEmail());
        $this->assertEquals('password123', $user->getPassword());
        $this->assertTrue($user->isVerified());
    }

    public function testUserRoles(): void
    {
        $user = new User();
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];

        $user->setRoles($roles);

        $this->assertEquals($roles, $user->getRoles());
    }

    public function testUserReservations(): void
    {
        $user = new User();
        $reservation = $this->createMock(Reservation::class);

        // Ajouter une réservation
        $user->addReservation($reservation);
        $this->assertCount(1, $user->getReservations());
        $this->assertTrue($user->getReservations()->contains($reservation));


        // Supprimer une réservation
        $user->removeReservation($reservation);
        $this->assertCount(0, $user->getReservations());
        $this->assertFalse($user->getReservations()->contains($reservation));

    }

    public function testUserEvents(): void
    {
        $user = new User();
        $event = $this->createMock(Events::class);

        // Ajouter un événement
        $user->addEvent($event);
        $this->assertCount(1, $user->getEvent());
        $this->assertTrue($user->getEvent()->contains($event));

        // Supprimer un événement
        $user->removeEvent($event);
        $this->assertCount(0, $user->getEvent());
        $this->assertFalse($user->getEvent()->contains($event));
    }

    public function testPasswordResetTokens(): void
    {
        $user = new User();
        $passwordResetToken = $this->createMock(PasswordResetToken::class);

        // Ajouter un token de réinitialisation de mot de passe
        $user->addPasswordResetToken($passwordResetToken);
        $this->assertCount(1, $user->getPasswordResetTokens());
        $this->assertTrue($user->getPasswordResetTokens()->contains($passwordResetToken));

        // Supprimer un token de réinitialisation de mot de passe
        $user->removePasswordResetToken($passwordResetToken);
        $this->assertCount(0, $user->getPasswordResetTokens());
        $this->assertFalse($user->getPasswordResetTokens()->contains($passwordResetToken));
    }

}
