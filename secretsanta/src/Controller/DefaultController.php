<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\User;
use App\Entity\Event;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_default')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/DefaultController.php',
        ]);
    }
    #[Route('/gift/:id')]
    public function makeGift($userId, EntityManagerInterface $entityManager, Request $request) {

        $user = $entityManager->getRepository(User::class)->find($userId);  
        $event = $entityManager->getRepository(Event::class)->findAll();
        if ($user->getHasGift()) {
            if ($request->isMethod('POST')) {
                $gift = new Gift();
                $gift->setMessage($request->request->get('message'));
                $gift->setUserId($userId);
                $gift->getPrice($request->request->get('price'));
                $gift->setName($request->request->get('name'));
                $gift->setVerified(0);
                $gift->setIdEvent($request->request->get('eventId'));

                $entityManager->persist($gift);
                $this->addFlash('success', 'Cadeau envoyé ! En attente de confirmation administrateur.'); 
                return $this->redirectToRoute('app_login');
            }
        }
        $this->addFlash('error', 'Vous n\'avez pas de crédits'); 
    }

    #[Route('/users')]
    public function usersPage(
        UserRepository $userRepository
        , PaginatorInterface $paginator,
        Request $request) {
        $data = $userRepository->findAll();  
        $users = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            6
        );
        
        return $this->render('users.html.twig', [
            'users' => $users
        ]);
    }
}
