<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\EventsRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation/{id}', name: 'event_reservation', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function register(int $id,EventsRepository $er,UserRepository $ur,EntityManagerInterface $em): Response

    {   $reservation = new Reservation();
        $event =$er->find($id);
        $user = $this->getUser();
        $reservation->setEvent($event);
        $reservation->setDate($event->getDate());
        $reservation->setUser($user);
        $reservation->setStatus("Not Payed");


        // Persist the reservation to the database
        //$entityManager = $this->getDoctrine()->getManager();
        $em->persist($reservation);
        $em->flush();
        return $this->redirectToRoute('app_event');
    }
    #[Route('/new', name: 'reservation_new')]
    public function new(Request $request,EntityManagerInterface $em):Response
    {  $reservation = new Reservation();
        $form = $this->createForm(ReservationFormType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() ) {
            $em->persist($reservation);
            $em->flush();
            return $this->redirectToRoute('app_event');
        }
        return $this->render('event/new.html.twig', [
            'formE' => $form->createView(),
        ]);
    }
}
