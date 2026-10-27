<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Création du compte d'Ina Zaoui
        $admin = new User();
        $admin->setEmail("ina@zaoui.com");
        $admin->setPassword($this->hasher->hashPassword($admin, "password"));
        $admin->setName("Ina Zaoui");
        $admin->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);

        // Création des invités
        $guests = [];
        for ($i = 0; $i < 100; $i++) {
            $guest = new User();
            $guest->setEmail("invite+" . $i . "@example.com");
            $guest->setPassword($this->hasher->hashPassword($guest, "password"));
            $guest->setName("Invité " . $i);
            $guest->setDescription("Le maître de l'urbanité capturée, explore les méandres des cités avec un regard vif et impétueux, figeant l'énergie des rues dans des instants éblouissants. À travers une technique avant-gardiste, il métamorphose le béton et l'acier en toiles abstraites, révélant l'essence même de l'architecture moderne. Ses clichés transcendent les formes familières pour révéler des perspectives inattendues, offrant une vision nouvelle et captivante du monde urbain.");
            $manager->persist($guest);
            $guests[] = $guest;
        }

        // Création des albums 
        $albums = [];
        for ($i = 1; $i < 6; $i++) {
            $album = new Album();
            $album->setName("Album " . $i);
            $manager->persist($album);
            $albums[] = $album;
        }

        // Création des médias de l'admin 
        $number = 1;
        for ($i = 0; $i < 50; $i++) {
            $media = new Media();
            $media->setUser($admin);
            $media->setAlbum($albums[intdiv($i, 10)]);
            $media->setPath($this->getCorrectMediaPath($number++));
            $media->setTitle("Titre " . $i);
            $manager->persist($media);
        }

        // Création des médias des invités 
        foreach ($guests as $guest) {
            for ($i = 0; $i < 50; $i++) {
                $media = new Media();
                $media->setUser($guest);
                $media->setPath($this->getCorrectMediaPath($number++));
                $media->setTitle('Titre ' . $i);
                $manager->persist($media);
            }
        }

        $manager->flush();
    }

    private function getCorrectMediaPath(int $number): string
    {
        return sprintf("uploads/%04d.jpg", $number);
    }
}
