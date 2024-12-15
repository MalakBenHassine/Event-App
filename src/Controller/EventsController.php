<?php

namespace App\Controller;

use Doctrine\DBAL\Types\Types;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EventsController extends AbstractController
{

    #[Route('/events', name: 'app_event')]
    public function listEvents(EventsRepository $er): Response
    {
        $events = $er->findAll();
        return $this->render('events/listEvents.html.twig', [
            'events' => $events,
            'field' => "",
            'value' => "",
        ]);
    }

    #[Route('/events/filter', name: 'app_event_filter', methods: ['GET'])]
    public function filterEvents(Request $request, EventsRepository $er): Response
    {
        $field = $request->query->get('field');
        $value = $request->query->get('value');

        if ($field && $value) {
            $events = $er->filterByField($field, $value);
        } else {
            $events = $er->findAll();
        }

        return $this->render('events/listEvents.html.twig', [
            'events' => $events,
            'field' => $field,
            'value' => $value,
        ]);
    }
    #[Route('/events/new', name: 'app_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $event = new Events();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('app_event');
        }

        return $this->render('events/new.html.twig', [
            'formE' => $form->createView(),
        ]);
    }


    #[Route('/event/edit/{id}', name: 'app_event_edit')]
    public function edit(Events $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire avec l'entité existante
        $form = $this->createForm(EventType::class, $event);

        // Traitement de la requête
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement des modifications
            $entityManager->flush();

            // Redirection après succès
            return $this->redirectToRoute('app_event');
        }

        return $this->render('events/editEvent.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }



    // Delete Event
    #[Route('/events/delete/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function deleteEvent(Request $request, Events $event, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
        }

        return $this->redirectToRoute('app_event');
    }
}
