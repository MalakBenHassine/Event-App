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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/local')]
final class LocalController extends AbstractController
{
    #[Route(name: 'app_local_index', methods: ['GET'])]
    public function index(LocalRepository $localRepository): Response
    {
        return $this->render('local/index.html.twig', [
            'locals' => $localRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_LOUEUR')]
    #[Route('/new', name: 'app_local_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em)
    {
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
            $em->persist($local);
            $em->flush();

            return $this->redirectToRoute('app_local_index');
        }

        return $this->render('local/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_LOUEUR')]
    #[Route('/{id}/edit', name: 'app_local_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Local $local, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LocalType::class, $local);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_local_index');
        }

        return $this->render('local/edit.html.twig', [
            'local' => $local,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_LOUEUR')]
    #[Route('/{id}', name: 'app_local_delete', methods: ['POST'])]
    public function delete(Request $request, Local $local, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $local->getId(), $request->request->get('_token'))) {
            $entityManager->remove($local);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_local_index');
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
