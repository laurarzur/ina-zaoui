<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;

class SecurityControllerTest extends FunctionalTestCase
{
    public function testLoginPage(): void
    {
        $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testAdminLogin(): void
    {
        $this->logUser('ina@zaoui.com', 'password');

        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->client->request('GET', '/admin/media/');
        $this->assertResponseIsSuccessful();
    }

    public function testGuestLogin(): void
    {
        $this->logUser('invite1@example.com', 'password');

        $this->assertResponseRedirects();
        $this->client->request('GET', '/admin/media/');
        $this->assertResponseIsSuccessful();
    }

    public function testWrongPassword(): void
    {
        $this->logUser('ina@zaoui.com', 'mauvais-mot-de-passe');

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testUnknownEmail(): void
    {
        $this->logUser('inconnu@example.com', 'password');

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testBlockedGuest(): void
    {
        $this->logUser('bloque@example.com', 'password');

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'bloqué');

        $this->client->request('GET', '/admin/media/');
        $this->assertResponseRedirects('/login');
    }

    public function testInvalidCsrfToken(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'ina@zaoui.com',
            '_password' => 'password',
        ]);
        $form['_csrf_token'] = 'jeton-invalide';

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    public function testLogout(): void
    {
        $this->logUser('ina@zaoui.com', 'password');
        $this->client->request('GET', '/admin/media/');
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/logout');

        $this->client->request('GET', '/admin/media/');
        $this->assertResponseRedirects('/login');
    }

    private function logUser(string $email, string $password): void
    {
        $this->client->request('GET', '/login');
        $this->client->submitForm('Connexion', [
            '_username' => $email,
            '_password' => $password,
        ]);
    }
}
