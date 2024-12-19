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




            // Créer l'e-mail
            $emailMessage = (new Email())
                ->from('noreply@example.com') // Remplacez par votre adresse e-mail
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html('<p>Voici le lien pour réinitialiser votre mot de passe : <a href="' . $resetLink . '">' . $resetLink . '</a></p>');

            // Envoyer l'e-mail
            $mailer->send($emailMessage);
        }

        // Renvoyer une réponse (par exemple, un message de confirmation)
        return $this->render('security/forgot_password.html.twig', [
            'message' => 'Si un utilisateur avec cet e-mail existe, un lien de réinitialisation a été envoyé.',
        ]);
    }


    #[Route('/request-password-reset', name: 'app_request_password_reset')]
    public function requestPasswordReset(
        Request $request,
        UserRepository $userRepository,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token
                $resetToken = bin2hex(random_bytes(32));

                // Création de l'entité PasswordResetToken
                $passwordResetToken = new PasswordResetToken();
                $passwordResetToken->setUser($user);
                $passwordResetToken->setToken($resetToken);
                $passwordResetToken->setExpiresAt(new \DateTime('+1 hour')); // Token valable 1 heure

                // Enregistrer le token dans la base de données
                $entityManager->persist($passwordResetToken);
                $entityManager->flush();

                // Créer un lien de réinitialisation
                $resetLink = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $resetToken],
                    \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL
                );




                // Créer l'e-mail
                $emailMessage = (new Email())
                    ->from('noreply@example.com') // Remplacez par votre adresse e-mail
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html('<p>Voici le lien pour réinitialiser votre mot de passe : <a href="' . $resetLink . '">' . $resetLink . '</a></p>');

                // Envoyer l'e-mail
                $mailer->send($emailMessage);

                // Afficher un message de succès
                $this->addFlash('success', 'Si un compte avec cet e-mail existe, un lien de réinitialisation a été envoyé.');
            } else {
                $this->addFlash('error', 'Aucun utilisateur trouvé avec cette adresse e-mail.');
            }

            return $this->redirectToRoute('app_request_password_reset');
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        PasswordResetTokenRepository $passwordResetTokenRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérifier si le token est valide
        $passwordResetToken = $passwordResetTokenRepository->findOneBy(['token' => $token]);

        if (!$passwordResetToken || $passwordResetToken->getExpiresAt() < new \DateTime()) {
            // Le token est invalide ou expiré
            $this->addFlash('error', 'Le lien de réinitialisation a expiré ou est invalide.');
            return $this->redirectToRoute('app_request_password_reset');
        }

        // Si le token est valide, afficher le formulaire de réinitialisation du mot de passe
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password');

            // Mettre à jour le mot de passe de l'utilisateur
            $user = $passwordResetToken->getUser();
            $user->setPassword(password_hash($newPassword, PASSWORD_BCRYPT));

            // Supprimer le token après l'utilisation
            $entityManager->remove($passwordResetToken);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }

}
