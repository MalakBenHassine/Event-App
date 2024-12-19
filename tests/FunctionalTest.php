<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
class FunctionalTest extends WebTestCase
{

    public function testUserRegistration(): void
    {
        // Crée un client pour simuler une requête HTTP
        $client = static::createClient();

        // Accède à la page d'enregistrement
        $crawler = $client->request('GET', '/register');

        // Vérifie que la page d'enregistrement s'affiche correctement
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');

        // Remplir le formulaire d'enregistrement
        $form = $crawler->selectButton('Register')->form();

        // Remplir les champs du formulaire avec des données valides
        $form['registration_form[username]'] = 'TestUser';
        $form['registration_form[email]'] = 'testuser@example.com';
        $form['registration_form[plainPassword]'] = 'Password123!';
        $form['registration_form[agreeTerms]'] = true;

        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier la redirection après soumission
        $this->assertResponseRedirects('/login');

        // Vérifier si l'utilisateur a été créé dans la base de données
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'testuser@example.com']);

        $this->assertNotNull($user);
        $this->assertEquals('TestUser', $user->getUsername());
        $this->assertEquals('testuser@example.com', $user->getEmail());
    }

    public function testInvalidUserRegistration(): void
    {
        // Crée un client pour simuler une requête HTTP
        $client = static::createClient();

        // Accède à la page d'enregistrement
        $crawler = $client->request('GET', '/register');

        // Remplir le formulaire avec des données invalides
        $form = $crawler->selectButton('Register')->form();
        $form['registration_form[username]'] = ''; // Nom d'utilisateur vide
        $form['registration_form[email]'] = 'invalid-email'; // Email invalide
        $form['registration_form[plainPassword]'] = 'short'; // Mot de passe trop court
        $form['registration_form[agreeTerms]'] = false; // Non-acception des termes

        // Soumettre le formulaire
        $client->submit($form);

        // Vérifier que le formulaire a des erreurs
        $this->assertResponseIsSuccessful(); // La page doit se recharger
        $this->assertSelectorTextContains('.form-error', 'This value should not be blank.');
        $this->assertSelectorTextContains('.form-error', 'This value is not a valid email address.');
        $this->assertSelectorTextContains('.form-error', 'This value is too short.');
        $this->assertSelectorTextContains('.form-error', 'You must agree to the terms.');
    }


}
