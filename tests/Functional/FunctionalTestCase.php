<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
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
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }

    protected function loginAdmin(): void
    {
        $this->client->loginUser($this->user('ina@zaoui.com'));
    }
}
