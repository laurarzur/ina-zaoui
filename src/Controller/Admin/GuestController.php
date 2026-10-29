<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class GuestController extends AbstractController
{
    public function __construct(private UserRepository $repo, private EntityManagerInterface $em) {}

    /**
     * Cette méthode permet d'afficher la liste des invités sur le Back Office
     * @return Response
     */
    #[Route('/admin/guest', name: 'admin_guest_index')]
    public function index(): Response
    {
        $guests = $this->repo->findGuests();

        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
        ]);
    }

    /**
     * Cette méthode permet de créer un invité
     * @param Request $request 
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $guest->setPassword($hasher->hashPassword($guest, $plainPassword));

            $this->em->persist($guest);
            $this->em->flush();

            return $this->redirectToRoute('admin_guest_index');
        }
        return $this->render('admin/guest/add.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * Cette méthode permet de modifier le statut (bloqué ou non) d'un invité 
     * @param bool $value
     * @param User $guest
     * @return Response
     */
    private function setGuestStatus(bool $value, User $guest): Response
    {
        $guest->setIsBlocked($value);
        $this->em->persist($guest);
        $this->em->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    /**
     * Cette méthode permet de bloquer un invité 
     * @param User $guest
     * @return Response
     */
    #[Route('/admin/guest/block/{id}', name: 'admin_guest_block')]
    public function block(User $guest): Response
    {
        return $this->setGuestStatus(true, $guest);
    }

    /**
     * Cette méthode permet de débloquer un invité 
     * @param User $guest
     * @return Response
     */
    #[Route('/admin/guest/unblock/{id}', name: 'admin_guest_unblock')]
    public function unblock(User $guest): Response
    {
        return $this->setGuestStatus(false, $guest);
    }

    /**
     * Cette méthode permet de supprimer un invité 
     * @param User $guest
     * @return Response
     */
    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete')]
    public function delete(User $guest): Response
    {
        $this->em->remove($guest);
        $this->em->flush();
        return $this->redirectToRoute('admin_guest_index');
    }
}
