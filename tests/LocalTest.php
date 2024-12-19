<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Local;
use App\Entity\Events;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class LocalTest extends TestCase
{
    public function testLocalAttributes()
    {
        $local = new Local();

        $name = "Conference Room A";
        $adr = "123 Main Street";
        $capacite = 50;
        $description = "A spacious conference room.";
        $price = 150.75;
        $isAvailable = false;

        $local->setName($name)
            ->setAdr($adr)
            ->setCapacite($capacite)
            ->setDescription($description)
            ->setPrice($price)
            ->setIsAvailable($isAvailable);

        $this->assertEquals($name, $local->getName());
        $this->assertEquals($adr, $local->getAdr());
        $this->assertEquals($capacite, $local->getCapacite());
        $this->assertEquals($description, $local->getDescription());
        $this->assertEquals($price, $local->getPrice());
        $this->assertFalse($local->isAvailable());
    }

    public function testLocalRelations()
    {
        $local = new Local();

        // Test relation avec User
        $user = new User();
        $local->setUser($user);

        $this->assertSame($user, $local->getUser());

        // Test relation avec Events
        $event1 = new Events();
        $event2 = new Events();

        $local->addEvent($event1);
        $local->addEvent($event2);

        $this->assertCount(2, $local->getEvents());
        $this->assertTrue($local->getEvents()->contains($event1));
        $this->assertTrue($local->getEvents()->contains($event2));

        $local->removeEvent($event1);
        $this->assertCount(1, $local->getEvents());
        $this->assertFalse($local->getEvents()->contains($event1));
    }

    public function testLocalInitialState()
    {
        $local = new Local();

        $this->assertNull($local->getId());
        $this->assertNull($local->getName());
        $this->assertNull($local->getAdr());
        $this->assertNull($local->getCapacite());
        $this->assertNull($local->getDescription());
        $this->assertNull($local->getPrice());
        $this->assertTrue($local->isAvailable());
        $this->assertInstanceOf(ArrayCollection::class, $local->getEvents());
        $this->assertEmpty($local->getEvents());
        $this->assertNull($local->getUser());
    }
}
