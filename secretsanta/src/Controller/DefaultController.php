<?php

namespace App\Controller;

use App\Entity\Gift;
use App\Entity\User;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\GiftRepository;
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
    #[Route('/gift/{id}', name: 'gift_to_user')]
    public function makeGift($id, EntityManagerInterface $entityManager, Request $request) {
        $userAuthenticated = $this->getUser();

        $user = $entityManager->getRepository(User::class)->find($id); 
 
        if ($user->isHasCredit()) {
            if ($request->isMethod('POST')) {
                $gift = new Gift();
                $gift->setMessage($request->request->get('message'));
                $gift->setUserId($user);
                $gift->setPrice($request->request->get('price'));
                $gift->setName($request->request->get('name'));
                $gift->setVerified(0);
                $event = $entityManager->getRepository(Event::class)->find($request->request->get('eventId'));  
                $gift->setIdEvent($event);

                $entityManager->persist($gift);
                $user->addIdGift($gift);
                $userAuthenticated->setHasCredit(0);
                $entityManager->persist($gift);
                $entityManager->persist($user);
                $entityManager->persist($userAuthenticated);

                $entityManager->flush();

                $this->addFlash('success', 'Cadeau envoyé ! En attente de confirmation administrateur.'); 
                return $this->redirectToRoute('gift_to_user', ['id' => $id]);
            }
        }
        return $this->redirectToRoute('gift_to_user', ['id' => $id]);

        $this->addFlash('error', 'Vous n\'avez pas de crédits'); 
    }

    #[Route('/users')]
    public function usersPage(
        UserRepository $userRepository
        , GiftRepository $giftRepository, PaginatorInterface $paginator,
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

    #[Route('/user/{id}')]
    public function userProfile($id,
        UserRepository $userRepository,
        EventRepository $eventRepository,
        Request $request) {
        $data = $userRepository->find($id);  
        $event = $eventRepository->findAll();

        return $this->render('user-profile.html.twig', [
            'user' => $data,
            'event' => $event
        ]);
    }
}
