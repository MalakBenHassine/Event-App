<?php

namespace App\Controller;

use App\Entity\Local;
use App\Form\LocalType;
use App\Repository\LocalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/local')]
final class LocalController extends AbstractController
{
    #[Route('/myLocals', name: 'app_my_locals',  methods: ['GET'])]
    public function myLocals(LocalRepository $localRepository, Security $security): Response
    {
        $user = $security->getUser();
        $locals = $localRepository->findByUser($user);
        if (empty($locals)) {
            throw $this->createNotFoundException("No locals found for the current user.");
        }
        return $this->render('local/myLocals.html.twig', [
            'locals' => $locals,
        ]);
    }
    #[Route(name: 'app_local_index', methods: ['GET'])]
    public function index(LocalRepository $localRepository): Response
    {
        return $this->render('local/index.html.twig', [
            'locals' => $localRepository->findAll(),
        ]);
    }
    #[IsGranted('ROLE_LOCATOR')]

    #[Route('/new', name: 'app_local_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        // Vérifie si l'utilisateur possède le rôle "ROLE_LOCATOR"
        if (!$this->isGranted('ROLE_LOCATOR')) {
            throw $this->createAccessDeniedException('Vous n’avez pas les droits pour créer un Local.');
        }

        $local = new Local();
        $user = $this->getUser();

        if ($user instanceof UserInterface) {
            $local->setUser($user);
        } else {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($local);
            $entityManager->flush();

            return $this->redirectToRoute('app_local_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('local/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }






    #[IsGranted('ROLE_LOCATOR')]

    #[Route('/{id}/edit', name: 'app_local_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Local $local, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_my_locals', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('local/edit.html.twig', [
            'local' => $local,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_LOCATOR')]

    #[Route('/{id}', name: 'app_local_delete', methods: ['POST'])]
    public function delete(Request $request, Local $local, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $local->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($local);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_local_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_local_show', methods: ['GET'])]
    public function show(Local $local): Response
    {
        return $this->render('local/show.html.twig', [
            'local' => $local,
        ]);
    }




    #[Route('/local/disponibles', name: 'app_local_available', methods: ['GET'])]
    public function available(LocalRepository $localRepository): Response
    {
        $locals = $localRepository->findAvailableLocals();

        return $this->render('local/available.html.twig', [
            'locals' => $locals,
        ]);
    }
}
