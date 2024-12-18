<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\PasswordResetToken;
use App\Repository\PasswordResetTokenRepository;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/loginl.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
    #[Route(path: '/home', name: 'app_home')]
    public function home(): Response
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        return $this->render('Home.html.twig',
        );
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        return $this->render('security/Succes.html.twig',
        );
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
