<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\User;
use App\Repository\AlbumRepository;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(private UserRepository $userRepo, private AlbumRepository $albumRepo, private MediaRepository $mediaRepo) {}

    /**
     * Cette méthode permet d'afficher la page d'accueil
     * @return Response
     */
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * Cette méthode permet d'afficher la liste des invités non bloqués
     * @return Response
     */
    #[Route('/guests', name: 'guests')]
    public function guests(): Response
    {
        $guests = $this->userRepo->findActiveGuests();
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * Cette méthode permet d'afficher la page d'un invité
     * @param User $guest
     * @return Response
     */
    #[Route('/guest/{id}', name: 'guest')]
    public function guest(User $guest): Response
    {
        if ($guest->isBlocked()) {
            return $this->redirectToRoute('guests');
        }

        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * Cette méthode permet d'afficher le portfolio d'Ina Zaoui 
     * @param ?Album $album
     * @return Response
     */
    #[Route('/portfolio/{id}', name: 'portfolio', defaults: ['id' => null])]
    public function portfolio(?Album $album = null): Response
    {
        $albums = $this->albumRepo->findAll();

        // Si un album est demandé, les médias de cet album sont récupérés 
        // Sinon, ce sont tous les médias de l'admin
        $medias = $album
            ? $this->mediaRepo->findBy(['album' => $album])
            : $this->mediaRepo->findBy(['user' => $this->userRepo->findAdmin()]);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * Cette méthode permet d'afficher la page "Qui suis-je ?"
     * @return Response
     */
    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
