<?php

namespace App\Controller;

use App\Entity\Events;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation/{id}', name: 'event_reservation', methods: ['GET', 'POST'])]
    public function reserve(int $id, Request $request, EntityManagerInterface $em): Response
    {
        // Trouver l'événement
        $event = $em->getRepository(Events::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour réserver.');
            return $this->redirectToRoute('app_login');
        }

        // Créer une nouvelle réservation
        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser($user);
        $reservation->setStatus('Pending'); // Par défaut, la réservation est en attente
        $reservation->setDate(new \DateTime()); // La date de la réservation est maintenant

        // Créer et traiter le formulaire
        $form = $this->createFormBuilder($reservation)
            ->add('Date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'data' => $event->getDate(), // Prendre la date de l'événement
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la réservation
            $em->persist($reservation);
            $em->flush();

            // Ajouter un message de confirmation
            $this->addFlash('success', 'Votre réservation a été confirmée !');

            // Rediriger vers la page de l'événement
            return $this->redirectToRoute('app_user_event_list', ['id' => $event->getId()]);
        }

        // Rendre la vue du formulaire
        return $this->render('events/reservation.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
    #[Route('/event/{id}/reservations', name: 'admin_event_reservations')]
    public function listReservations(int $id, EntityManagerInterface $em): Response
    {

        // Trouver l'événement
        $event = $em->getRepository(Events::class)->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        // Récupérer toutes les réservations de cet événement
        $reservations = $em->getRepository(Reservation::class)->findBy(['event' => $event]);

        // Passer les réservations et l'événement à la vue
        return $this->render('reservation/liste_reservations.html.twig', [
            'event' => $event,
            'reservations' => $reservations,
        ]);
    }
}
