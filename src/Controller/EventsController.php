<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Repository\LocalRepository;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use DateTime;
use Doctrine\DBAL\Types\Types;
use App\Form\LocalType;
use Symfony\Component\Security\Core\Security;

use App\Entity\Events;
use App\Form\EventType;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Local;

class EventsController extends AbstractController
{


    /*#[Route('/home', name: 'app_home')]
    public function home(EventsRepository $er): Response
    {  $events = $er->findAll();
        return $this->render('events/Home.html.twig',[
           'events' => $events,
        ]);
    }*/


    #[Route('/events', name: 'app_event')]
    public function listEvents(Request $request, EventsRepository $er,LocalRepository $localRepository): Response

    {  // $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $name = $request->query->get('name');
        $dateFromString = $request->query->get('date_from');
        $category = $request->query->get('category');
        $local = $request->query->get('local');
        $dateFrom = null;

        if ($dateFromString) {
            try {
                $dateFrom = new DateTime($dateFromString);
            } catch (Exception $e) {
                // Handle invalid date format if necessary
                $this->addFlash('error', 'Invalid date format.');
            }
        }

        $events = $er->findByFilters($name, $category, $local, $dateFrom);
        $locations = $localRepository->findAll();
        //$events = $er->findAll();
        return $this->render('events/shows-events.html.twig', [
            'events' => $events,
            'field' => "",
            'value' => "",
            "locations"=>$locations
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
    public function new(Request $request, EntityManagerInterface $entityManager, LocalRepository $localRepository): Response
    {
        // Créer une nouvelle instance d'événement
        $event = new Events();

        // Créer le formulaire pour l'entité Event
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        // Récupérer tous les locaux disponibles
        $locals = $localRepository->findAll();

        // Traiter la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Validation supplémentaire des champs si nécessaire
            if (!$event->getNom()) {
                $form->get('nom')->addError(new FormError('Le nom est requis.'));
            }
            if (!$event->getDate()) {
                $form->get('date')->addError(new FormError('La date est requise.'));
            }
            if (!$event->getLocal()) {
                $form->get('local')->addError(new FormError('Le local est requis.'));
            }

            // Si le formulaire est valide, on sauvegarde l'événement
            if ($form->isValid()) {
                // Si un local est sélectionné, vérifier s'il est déjà enregistré
                $local = $event->getLocal();

                // Si le local est une nouvelle instance (non enregistré), on le persiste dans la base de données
                if ($local && !$local->getId()) {
                    $entityManager->persist($local);
                }

                // Marquer le local comme indisponible si un local est sélectionné
                if ($local) {
                    $local->setIsAvailable(false);
                    $entityManager->persist($local);
                }

                // Sauvegarder l'événement en base de données
                $entityManager->persist($event);
                $entityManager->flush();

                // Rediriger vers la page des événements après la création de l'événement
                return $this->redirectToRoute('app_event');
            }
        }

        // Rendre le formulaire dans la vue avec la liste des locaux disponibles
        return $this->render('events/addevents.html.twig', [
            'form' => $form->createView(),
            'locals' => $locals,  // Affichage des locaux
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
            // Remettre le local en disponibilité
            $local = $event->getLocal();
            if ($local) {
                $local->setIsAvailable(true);
                $em->persist($local);
            }

            $em->remove($event);
            $em->flush();
        }

        return $this->redirectToRoute('app_event');
    }



    #[Route('/local/{id}', name: 'app_local_detail')]
    public function localDetail(string $id, EntityManagerInterface $em, Request $request, Security $security): Response
    {
        $user = $security->getUser();
        // Vérification si l'ID est 'new'
        if ($id === 'new') {
            // Créer un nouveau local (entité)
            $local = new Local();

            // Associer le local à l'utilisateur connecté
            $user = $security->getUser();  // Récupère l'utilisateur connecté
            $local->setUser($user); // Lier l'utilisateur au local

            // Créer le formulaire lié à cet objet
            $form = $this->createForm(LocalType::class, $local);

            // Traiter la soumission du formulaire
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Sauvegarder le nouveau local en base de données
                $em->persist($local);
                $em->flush();

                // Rediriger vers la page de détail du nouveau local (ou vers une autre page)
                return $this->redirectToRoute('app_local_detail', ['id' => $local->getId()]);
            }

            // Afficher le formulaire de création dans la vue
            return $this->render('local/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        // Traiter le cas où l'ID est un entier et afficher les détails du local existant
        $local = $em->getRepository(Local::class)->find((int) $id);

        if (!$local) {
            throw $this->createNotFoundException('Le local n\'existe pas');
        }

        return $this->render('local/detail.html.twig', [
            'local' => $local,
        ]);
    }




}






