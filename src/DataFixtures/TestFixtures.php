<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {

        // Création du compte admin
        $admin = new User();
        $admin->setEmail("ina@zaoui.com");
        $admin->setPassword($this->hasher->hashPassword($admin, "password"));
        $admin->setName("Ina Zaoui");
        $admin->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);

        // Création de quelques comptes invités
        $guests = [];
        foreach (
            [
                ['invite1@example.com', 'Invité 1', false],
                ['invite2@example.com', 'Invité 2', false],
                ['bloque@example.com', 'Invité bloqué', true],
            ] as [$email, $name, $blocked]
        ) {
            $guest = new User;
            $guest->setEmail($email);
            $guest->setPassword($this->hasher->hashPassword($guest, "password"));
            $guest->setName($name);
            $guest->setIsBlocked($blocked);
            $manager->persist($guest);
            $guests[] = $guest;
        }

        // Création de quelques albums 
        $albums = [];
        foreach (['Album 1', 'Album 2'] as $name) {
            $album = new Album();
            $album->setName($name);
            $manager->persist($album);
            $albums[] = $album;
        }

        // Création des médias de l'admin
        $n = 1;
        foreach ($albums as $album) {
            for ($i = 0; $i < 5; $i++) {
                $manager->persist($this->media($admin, 'Titre ' . $n, $n++, $album));
            }
        }

        // Création des médias des invités
        foreach ($guests as $guest) {
            for ($i = 0; $i < 3; $i++) {
                $manager->persist($this->media($guest, 'Titre ' . $i, $n++));
            }
        }

        $manager->flush();
    }

    private function media(User $user, string $title, int $number, ?Album $album = null): Media
    {
        $media = new Media();
        $media->setUser($user);
        $media->setAlbum($album);
        $media->setTitle($title);
        $media->setPath(sprintf('uploads/%04d.jpg', $number));

        return $media;
    }
}
