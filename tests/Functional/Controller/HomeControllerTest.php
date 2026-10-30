<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends FunctionalTestCase
{
    /**
     * @dataProvider staticPages
     */
    public function testStaticPages(string $route): void
    {
        $this->client->request('GET', $route);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function staticPages(): iterable
    {
        yield 'home' => ['/'];
        yield 'about' => ['/about'];
    }

    public function testGuestsPageDisplaysOnlyActiveGuests(): void
    {
        $this->client->request('GET', '/guests');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Invité 1');
        $this->assertSelectorTextContains('body', 'Invité 2');
        $this->assertSelectorTextNotContains('body', 'Invité bloqué');
    }

    public function testActiveGuestPage(): void
    {
        $guest = $this->user('invite1@example.com');
        $this->client->request('GET', '/guest/' . $guest->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Invité 1');
    }

    public function testBlockedGuestPage(): void
    {
        $blocked = $this->user('bloque@example.com');
        $this->client->request('GET', '/guest/' . $blocked->getId());

        $this->assertResponseRedirects('/guests');
    }

    public function testNonexistantGuestPage(): void
    {
        $this->client->request('GET', '/guest/-1');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPortfolioDisplaysAllPictures(): void
    {
        $crawler = $this->client->request('GET', '/portfolio');
        $link = $crawler->selectLink('Toutes');

        $class = $link->attr('class');
        $this->assertNotNull($class);
        $this->assertStringContainsString('active', $class);
        $this->assertCount(3, $crawler->filter('a.btn'));
        $this->assertCount(10, $crawler->filter('.media-title'));
    }

    public function testClickOnPortfolioFilter(): void
    {
        $crawler = $this->client->request('GET', '/portfolio');
        $crawler = $this->client->click($crawler->selectLink('Album 1')->link());

        $this->assertResponseIsSuccessful();
        $this->assertSame('Album 1', trim($crawler->filter('a.active')->text()));
        $this->assertCount(5, $crawler->filter('.media-title'));
    }
}
