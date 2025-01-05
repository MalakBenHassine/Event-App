<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Repository\EventsRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class ReservationController extends AbstractController
{   public function __construct(Security $security)
{
    $this->security = $security;
}
    #[Route('/ReservationForParticipant', name: 'My_Reservations')]
    public function listEventOfParticpant(ReservationRepository $er,): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->security->getUser();


        // Récupérer les événements auxquels cet utilisateur est inscrit
        $reservation = $er->findByUser((int)$user->getId());  // Ici, getEvents() récupère les événements associés à l'utilisateur
       // $reservation = $er->findAll();
        return $this->render('user/MyReservation.html.twig', [
            'reservations' => $reservation,
        ]);
    }
    #[Route('/reservation/{id}', name: 'show_event_participations')]
    public function showReservationPerEvent(int $id,EventsRepository $er,ReservationRepository $rr,UserRepository $ur,EntityManagerInterface $em): Response

    {
        $event =$er->find($id);
        $user = $this->security->getUser();
        $reservation=$rr->findByEvent($id);

        return $this->render('reservation/liste-reservations.html.twig',[
                "reservations" => $reservation,
                "event" => $event,
            ]
        );
    }
    #[Route('/reservations/{id}', name: 'make_reservation')]
    public function reserve(
        int $id,
        EventsRepository $er,
        UserRepository $ur,
        EntityManagerInterface $em
    ): Response {
        $event = $er->find($id);
        $user = $this->security->getUser();

        // Vérifier si une réservation existe déjà pour cet utilisateur et cet événement
        $existingReservation = $em->getRepository(Reservation::class)->findOneBy([
            'Event' => $event,
            'User' => $user,
        ]);

        if ($existingReservation) {
            $this->addFlash('warning', 'Vous avez déjà réservé pour cet événement.');
            return $this->redirectToRoute('event_details', ['id' => $id,'error' => 'Vous avez déjà réservé pour cet événement.']);
        }

        // Créer une nouvelle réservation
        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setDate($event->getDate());
        $reservation->setUser($user);
        $reservation->setStatus("Not Payed");

        // Enregistrer la réservation dans la base de données
        $em->persist($reservation);
        $em->flush();

       // $this->addFlash('success', 'Réservation effectuée avec succès.');

        return $this->redirectToRoute('My_Reservations', [
            "reservations" => $reservation,
            "event" => $event,
        ]);
    }

    #[Route('/new/{id}', name: 'reservation_new')]
    public function new(int $id,Request $request,EntityManagerInterface $em,EventsRepository $er):Response
    {   $reservation = new Reservation();
        $event =$er->find($id);
        $user = $this->security->getUser();
        $reservation->setEvent($event);
        $reservation->setDate($event->getDate());
        $reservation->setUser($user);
        $reservation->setStatus("Not Payed");

        return $this->redirectToRoute('app_event', [
        ]);
    }
    #[Route('/reservation/delete/{id}', name: 'reservation_delete', methods: ['POST'])]
    public function deleteReservation(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            // Remettre le local en disponibilité


            $em->remove($reservation);
            $em->flush();
        }

        return $this->redirectToRoute('My_Reservations');
    }
    #[Route('/reservation/confirm/{id}', name: 'reservation_confirm', methods: ['POST'])]
    public function confirmReservation(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            // Remettre le local en disponibilité


            $reservation->setStatus("Confirmed");
            $em->flush();
        }

        return $this->redirectToRoute('My_Reservations');
    }
}
