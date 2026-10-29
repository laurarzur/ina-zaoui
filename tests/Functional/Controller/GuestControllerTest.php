<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class GuestControllerTest extends FunctionalTestCase
{

    /**
     * @dataProvider protectedRoutes
     */
    public function testVisitor(string $route): void
    {
        $this->client->request('GET', $route);

        $this->assertResponseRedirects('/login');
    }

    /**
     * @dataProvider protectedRoutes
     */
    public function testGuestCannotAccess(string $route): void
    {
        $this->client->loginUser($this->user('invite1@example.com'));
        $this->client->request('GET', $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function protectedRoutes(): iterable
    {
        yield 'guests' => ['/admin/guest'];
        yield 'add' => ['/admin/guest/add'];
    }

    public function testListGuests(): void
    {
        $this->loginAdmin();

        $crawler = $this->client->request('GET', '/admin/guest');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Invité 1', $crawler->text());
        $this->assertStringContainsString('Invité 2', $crawler->text());
        $this->assertStringContainsString('Invité bloqué', $crawler->text());
    }

    public function testAddGuest(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/guest/add');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', [
            'guest[email]' => 'nouveau@example.com',
            'guest[name]' => 'Nouvel invité',
            'guest[plainPassword][first]' => 'abc123',
            'guest[plainPassword][second]' => 'abc123'
        ]);

        $this->assertResponseRedirects('/admin/guest');

        $guest = $this->user('nouveau@example.com');
        $this->assertNotSame('abc123', $guest->getPassword());
    }

    public function testAddInvalidGuest(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/guest/add');

        $this->client->submitForm('Ajouter', [
            'guest[email]' => '',
            'guest[name]' => '',
            'guest[plainPassword][first]' => 'abc123',
            'guest[plainPassword][second]' => '123abc'
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testBlockGuest(): void
    {
        $this->loginAdmin();
        $guest = $this->user('invite1@example.com');

        $this->client->request('GET', '/admin/guest/block/' . $guest->getId());

        $this->assertResponseRedirects('/admin/guest');
        $this->assertTrue($this->user('invite1@example.com')->isBlocked());
    }

    public function testUnblockGuest(): void
    {
        $this->loginAdmin();
        $guest = $this->user('bloque@example.com');

        $this->client->request('GET', '/admin/guest/unblock/' . $guest->getId());

        $this->assertResponseRedirects('/admin/guest');
        $this->assertFalse($this->user('bloque@example.com')->isBlocked());
    }

    public function testDeleteGuest(): void
    {
        $this->loginAdmin();
        $guest = $this->user('invite2@example.com');
        $id = $guest->getId();

        $this->client->request('GET', '/admin/guest/delete/' . $id);

        $this->assertResponseRedirects('/admin/guest');
        $this->assertNull(static::getContainer()->get(UserRepository::class)->find($id));
    }

    /**
     * @dataProvider actionsOnNonexistantGuest
     */
    public function testActionsOnNonexistantGuest(string $route): void
    {
        $this->loginAdmin();

        $this->client->request('GET', $route);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function actionsOnNonexistantGuest(): iterable
    {
        yield 'block' => ['/admin/guest/block/-1'];
        yield 'unblock' => ['/admin/guest/unblock/-1'];
        yield 'delete' => ['/admin/guest/delete/-1'];
    }
}
