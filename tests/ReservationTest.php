<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Reservation;
use App\Entity\User;
use App\Entity\Events;
use App\Entity\Tickets;

class ReservationTest extends TestCase
{
    public function testReservationAttributes()
    {
        $reservation = new Reservation();
        $date = new \DateTime('2024-12-19');
        $status = 'confirmed';

        $reservation->setDate($date);
        $reservation->setStatus($status);

        $this->assertEquals($date, $reservation->getDate());
        $this->assertEquals($status, $reservation->getStatus());
    }

    public function testReservationRelations()
    {
        $reservation = new Reservation();

        // Test User Relation
        $user = new User();
        $reservation->setUser($user);

        $this->assertSame($user, $reservation->getUser());

        // Test Event Relation
        $event = new Events();
        $reservation->setEvent($event);

        $this->assertSame($event, $reservation->getEvent());

        // Test Ticket Relation
        $ticket = new Tickets();
        $reservation->setTicket($ticket);

        $this->assertSame($ticket, $reservation->getTicket());
        $this->assertSame($reservation, $ticket->getReservation());
    }

    public function testReservationInitialState()
    {
        $reservation = new Reservation();

        $this->assertNull($reservation->getId());
        $this->assertNull($reservation->getDate());
        $this->assertNull($reservation->getStatus());
        $this->assertNull($reservation->getUser());
        $this->assertNull($reservation->getEvent());
        $this->assertNull($reservation->getTicket());
    }
}
