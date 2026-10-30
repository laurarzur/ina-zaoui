<?php

namespace App\Tests\Functional;

use App\Entity\Album;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function user(string $email): User
    {
        $user = $this->userRepo()->findOneBy(['email' => $email]);
        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    protected function album(string $name): Album
    {
        $album = $this->albumRepo()->findOneBy(['name' => $name]);
        $this->assertInstanceOf(Album::class, $album);

        return $album;
    }

    protected function loginAdmin(): void
    {
        $this->client->loginUser($this->user('ina@zaoui.com'));
    }

    protected function userRepo(): UserRepository
    {
        $repo = static::getContainer()->get(UserRepository::class);
        $this->assertInstanceOf(UserRepository::class, $repo);

        return $repo;
    }

    protected function albumRepo(): AlbumRepository
    {
        $repo = static::getContainer()->get(AlbumRepository::class);
        $this->assertInstanceOf(AlbumRepository::class, $repo);

        return $repo;
    }

    protected function mediaRepo(): MediaRepository
    {
        $repo = static::getContainer()->get(MediaRepository::class);
        $this->assertInstanceOf(MediaRepository::class, $repo);

        return $repo;
    }

    protected function entityManager(): EntityManagerInterface
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }
}
