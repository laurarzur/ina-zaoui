<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Media;
use App\Entity\User;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class MediaControllerTest extends FunctionalTestCase
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
     * @return iterable<string, array{string}>
     */
    public static function protectedRoutes(): iterable
    {
        yield 'medias' => ['/admin/media/'];
        yield 'add' => ['/admin/media/add'];
    }

    public function testAdminSeesAllMedias(): void
    {
        $this->loginAdmin();

        $crawler = $this->client->request('GET', '/admin/media/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(19, $crawler->filter('tbody tr'));
    }

    public function testGuestOnlySeesOwnMedias(): void
    {
        $this->client->loginUser($this->user('invite1@example.com'));

        $crawler = $this->client->request('GET', '/admin/media/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $crawler->filter('tbody tr'));
    }

    public function testSecondPageIsEmpty(): void
    {
        $this->loginAdmin();

        $crawler = $this->client->request('GET', '/admin/media/?page=2');

        $this->assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('tbody tr'));
    }

    public function testInvalidPageFallsBackToFirst(): void
    {
        $this->loginAdmin();

        $this->client->request('GET', '/admin/media/?page=0');

        $this->assertResponseIsSuccessful();
    }

    public function testFormFieldsDependOnRole(): void
    {
        $this->loginAdmin();
        $this->client->request('GET', '/admin/media/add');
        $this->assertSelectorExists('select[name="media[user]"]');

        $this->client->loginUser($this->user('invite1@example.com'));
        $this->client->request('GET', '/admin/media/add');
        $this->assertSelectorNotExists('select[name="media[user]"]');
    }

    public function testGuestAddsMedia(): void
    {
        $guest = $this->user('invite1@example.com');
        $this->client->loginUser($guest);
        $this->client->request('GET', '/admin/media/add');

        $this->client->submitForm('Ajouter', [
            'media[title]' => 'Ma photo',
            'media[file]' => dirname(__DIR__) . '/Fixtures/photo.jpg',
        ]);

        $this->assertResponseRedirects('/admin/media/');

        $media = $this->mediaRepo()->findOneBy(['title' => 'Ma photo']);
        $this->assertNotNull($media);

        $owner = $media->getUser();
        $this->assertInstanceOf(User::class, $owner);

        $this->assertSame($guest->getId(), $owner->getId());
    }

    public function testAdminDeletesAnyMedia(): void
    {
        $this->loginAdmin();
        $id = $this->mediaOf('invite1@example.com')->getId();

        $this->client->request('GET', '/admin/media/delete/' . $id);

        $this->assertResponseRedirects('/admin/media/');
        $this->assertNull($this->mediaRepo()->find($id));
    }

    public function testGuestDeletesOwnMedia(): void
    {
        $this->client->loginUser($this->user('invite1@example.com'));
        $id = $this->mediaOf('invite1@example.com')->getId();

        $this->client->request('GET', '/admin/media/delete/' . $id);

        $this->assertResponseRedirects('/admin/media/');
        $this->assertNull($this->mediaRepo()->find($id));
    }

    public function testDeleteUnknownMedia(): void
    {
        $this->loginAdmin();

        $this->client->request('GET', '/admin/media/delete/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function mediaOf(string $email): Media
    {
        $media = $this->mediaRepo()->findOneBy(['user' => $this->user($email)]);
        $this->assertInstanceOf(Media::class, $media);

        return $media;
    }
}
