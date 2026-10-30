<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->setEmail("email@email.com");
        $this->user->setPassword("abc123");
        $this->user->setName("Utilisateur test");
    }

    public function testGetEmailPasswordName(): void
    {
        $this->assertSame("email@email.com", $this->user->getEmail());
        $this->assertSame("abc123", $this->user->getPassword());
        $this->assertSame("Utilisateur test", $this->user->getName());
    }

    public function testSetDifferentEmailPasswordName(): void
    {
        $this->user->setEmail("email@example.com");
        $this->user->setPassword("123abc");
        $this->user->setName("Autre nom");
        $this->assertSame("email@example.com", $this->user->getEmail());
        $this->assertSame("123abc", $this->user->getPassword());
        $this->assertSame("Autre nom", $this->user->getName());
    }

    public function testGetUnnamedUser(): void
    {
        $unnamedUser = new User();
        $this->expectException(\Error::class);
        $name = $unnamedUser->getName();
    }

    public function testGetUserIdentifier(): void
    {
        $this->assertSame("email@email.com", $this->user->getUserIdentifier());
    }

    public function testGetRoles(): void
    {
        $this->assertSame(['ROLE_USER'], $this->user->getRoles());
    }

    public function testSetRoles(): void
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->assertEqualsCanonicalizing(['ROLE_ADMIN', 'ROLE_USER'], $this->user->getRoles());
        $this->assertCount(2, $this->user->getRoles());
    }

    public function testGetDescription(): void
    {
        $this->assertNull($this->user->getDescription());
    }

    public function testSetDescription(): void
    {
        $this->user->setDescription("Description");
        $this->assertSame("Description", $this->user->getDescription());
    }

    public function testIsBlocked(): void
    {
        $this->assertFalse($this->user->isBlocked());
    }

    public function testUserIsBlocked(): void
    {
        $this->user->setIsBlocked(true);
        $this->assertTrue($this->user->isBlocked());

        $this->user->setIsBlocked(false);
        $this->assertFalse($this->user->isBlocked());
    }

    public function testGetMedias(): void
    {
        $this->assertCount(0, $this->user->getMedias());
    }

    public function testAddMedia(): void
    {
        $media = new Media();

        $this->user->addMedia($media);
        $this->user->addMedia($media);

        $this->assertCount(1, $this->user->getMedias());
        $this->assertSame($this->user, $media->getUser());
    }

    public function testRemoveMedia(): void
    {
        $media = new Media();
        $this->user->addMedia($media);
        $this->user->removeMedia($media);
        $this->assertCount(0, $this->user->getMedias());
        $this->assertNull($media->getUser());
    }

    public function testCheckHashedPasswordInSession(): void
    {
        $data = $this->user->__serialize();
        $cle = "\0" . User::class . "\0password";

        $this->assertSame(hash("crc32c", "abc123"), $data[$cle]);
        $this->assertNotSame("abc123", $data[$cle]);
    }
}
