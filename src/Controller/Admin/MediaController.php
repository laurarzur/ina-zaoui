<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{

    public function __construct(private MediaRepository $repo, private EntityManagerInterface $em) {}

    /**
     * Cette méthode permet d'afficher la liste des médias sur le Back Office
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/media/', name: 'admin_media_index')]
    public function index(Request $request): Response
    {
        // Le numéro de page est récupéré pour la pagination
        $page = $request->query->getInt('page', 1);

        $limit = 25;
        $criteria = [];

        // Si l'utilisateur est un invité, seuls ses médias sont récupérés
        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->repo->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );
        $total = $this->repo->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * Cette méthode permet de créer un média
     * @param Request $request
     * @return Response
     */
    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $media = new Media();
        // Si c'est l'admin qui souhaite créer un média, plus de champs apparaissent dans le formulaire
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());
            $this->em->persist($media);
            $this->em->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form]);
    }

    /**
     * Cette méthode permet de supprimer un média 
     * @param Media $media
     * @return Response
     */
    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]
    public function delete(Media $media)
    {
        $this->em->remove($media);
        $this->em->flush();

        return $this->redirectToRoute('admin_media_index');
    }
}
