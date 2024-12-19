<?php

namespace App\Tests;

use App\Entity\Events;
use App\Entity\Local;

use PHPUnit\Framework\TestCase;

class UnitTest extends TestCase
{
    public function testEventsEntity(): void
    {
        // Création de l'objet Events
        $event = new Events();

        // Définir les valeurs
        $event->setNom('Symfony Workshop');
        $event->setDescription('An advanced Symfony workshop.');
        $date = new \DateTime('2024-12-20');
        $event->setDate($date);

        // Vérifier les getters
        $this->assertEquals('Symfony Workshop', $event->getNom());
        $this->assertEquals('An advanced Symfony workshop.', $event->getDescription());
        $this->assertSame($date, $event->getDate());
    }

    public function testEventsRelationWithLocal(): void
    {
        // Création de l'objet Events et Local
        $event = new Events();
        $local = new Local();

        // Simulation de la relation
        $event->setLocal($local);

        // Vérifier que la relation est correcte
        $this->assertSame($local, $event->getLocal());
    }
}
