<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AlbumController extends AbstractController
{

    public function __construct(private AlbumRepository $repo, private EntityManagerInterface $em) {}

    /**
     * Cette méthode permet d'afficher la liste des albums sur le Back Office
     * @return Response
     */
    #[Route('/admin/album/', name: 'admin_album_index')]
    public function index(): Response
    {
        $albums = $this->repo->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    /**
     * Cette méthode permet de créer un album
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/album/add', name: 'admin_album_add')]
    public function add(Request $request): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($album);
            $this->em->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form]);
    }

    /**
     * Cette méthode permet de modifier un album
     * @param Album $album
     * @return Response
     */
    #[Route('/admin/album/update/{id}', name: 'admin_album_update')]
    public function update(Request $request, Album $album): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($album);
            $this->em->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Cette méthode permet de supprimer un album
     * @param Album $album
     * @return Response
     */
    #[Route('/admin/album/delete/{id}', name: 'admin_album_delete')]
    public function delete(Album $album): Response
    {
        if ($album) {
            $this->em->remove($album);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_album_index');
    }
}
