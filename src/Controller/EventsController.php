<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\DBAL\Types\Types;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EventsController extends AbstractController
{



    #[Route('/home', name: 'app_home')]
    public function home(): Response
    {
        return $this->render('events/home.html.twig');
    }



    #[Route('/events', name: 'app_event')]
    public function listEvents(EventsRepository $er): Response

    {  // $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $events = $er->findAll();
        return $this->render('events/listEvents.html.twig', [
            'events' => $events,
            'field' => "",
            'value' => "",
        ]);
    }
    #[Route('/liste-des-events', name: 'app_user_event_list')]
    public function listUserEvents(EventsRepository $er): Response
    {
        $events = $er->findAll();

        return $this->render('events/user_listEvents.html.twig', [
            'events' => $events,
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
    #[Route('/send-reminder', name: 'app_send_reminder')]
    public function sendReminder(EmailVerifier $emailService): Response
    {
        $userEmail = 'user@example.com';
        $eventName = 'Symfony Workshop';
        $eventDate = new \DateTime('2024-09-10 10:00:00');

        $emailService->sendEventReminder($userEmail, $eventName, $eventDate);

        return new Response('Reminder email sent!');
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
    #[Route(path: '/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        // Logique pour récupérer l'utilisateur par e-mail
        $email = $request->request->get('email');
        $user = $userRepository->findOneBy(['email' => $email]);

        if ($user) {
            // Générer un token et l'enregistrer dans la base de données
            $resetToken = bin2hex(random_bytes(32));

            // Créer l'entité PasswordResetToken
            $passwordResetToken = new PasswordResetToken();
            $passwordResetToken->setUser($user);
            $passwordResetToken->setToken($resetToken);
            $passwordResetToken->setExpiresAt(new \DateTime('+1 hour')); // Token valable 1 heure

            // Enregistrer le token dans la base de données
            $entityManager->persist($passwordResetToken);
            $entityManager->flush();

            // Créer un lien de réinitialisation avec une URL absolue
            $resetLink = $this->generateUrl(
                'app_reset_password',
                ['token' => $resetToken],
                \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
            );






}
