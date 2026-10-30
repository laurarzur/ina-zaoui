<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Album;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class AlbumControllerTest extends FunctionalTestCase
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
        yield 'albums' => ['/admin/album/'];
        yield 'add' => ['/admin/album/add'];
    }

    public function testListAlbums(): void
    {
        $this->loginAdmin();

        $crawler = $this->client->request('GET', '/admin/album/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Album 1', $crawler->text());
        $this->assertStringContainsString('Album 2', $crawler->text());
    }

    public function testAddAlbum(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/album/add');
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Ajouter', ['album[name]' => 'Nouvel album']);

        $this->assertResponseRedirects('/admin/album/');
        $this->assertNotNull($this->albumRepo()->findOneBy(['name' => 'Nouvel album']));
    }

    public function testAddInvalidAlbum(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/album/add');

        $this->client->submitForm('Ajouter', ['album[name]' => '']);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNull($this->albumRepo()->findOneBy(['name' => '']));
    }

    public function testEditAlbum(): void
    {
        $this->loginAdmin();
        $album = $this->album('Album 2');

        $crawler = $this->client->request('GET', '/admin/album/update/' . $album->getId());
        $this->assertResponseIsSuccessful();
        $this->assertSame('Album 2', $crawler->filter('input[name="album[name]"]')->attr('value'));

        $this->client->submitForm('Modifier', ['album[name]' => 'Album renommé']);

        $this->assertResponseRedirects('/admin/album/');
        $this->assertNotNull($this->albumRepo()->findOneBy(['name' => 'Album renommé']));
        $this->assertNull($this->albumRepo()->findOneBy(['name' => 'Album 2']));
    }

    public function testEditNonexistantAlbum(): void
    {
        $this->loginAdmin();

        $this->client->request('GET', '/admin/album/update/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteAlbum(): void
    {
        $this->loginAdmin();
        $em = $this->entityManager();

        $album = new Album();
        $album->setName('Album à supprimer');
        $em->persist($album);
        $em->flush();
        $id = $album->getId();

        $this->client->request('GET', '/admin/album/delete/' . $id);

        $this->assertResponseRedirects('/admin/album/');
        $this->assertNull($this->albumRepo()->find($id));
    }

    public function testDeleteNonexistantAlbum(): void
    {
        $this->loginAdmin();

        $this->client->request('GET', '/admin/album/delete/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
