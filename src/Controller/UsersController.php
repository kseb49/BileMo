<?php

namespace App\Controller;

// use App\Entity\Clients;
use App\Repository\UsersRepository;
use App\Repository\ClientsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api')]
class UsersController extends AbstractController
{


    #[Route('/users', name: 'app_users', methods:'GET')]
    /**
     * Retrieve all the users linked to a client
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function usersList(UsersRepository $users, ClientsRepository $clients): JsonResponse
    {
        $client = $this->getUser();
        $userList = $users->findByClients($client);
        return $this->json($userList, context:['groups' => 'client_user']);

    }


    #[Route('/users/{id}', name: 'app_user', methods:'GET')]
    /**
     * Retrieve a single user
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function singleUser(UsersRepository $users, int $id): JsonResponse
    {
        $client = $this->getUser();
        $user = $users->findOneBy(['id' => $id,'clients' => $client]);
        if ($user === null) {
            return $this->json(["message" => "Vous n'avez pas d'utilisateurs avec cet identifiant"], 404);
        }
        return $this->json($user, context:['groups' => 'client_user']);

    }


    #[Route('/users', name:'create_user', methods:'POST')]
    #[IsGranted('ROLE_ADMIN', statusCode:403)]
    public function createUser(Request $request, EntityManager $entityManager)
    {

    }


    #[Route('/users/{id}', name:'delete_user', methods:'DELETE')]
    #[IsGranted('ROLE_ADMIN', message:"Vous n'avez pas les droits suffisants pour effectuer cet action", statusCode:403)]
    public function deleteUser(int $id, UsersRepository $users, EntityManagerInterface $entityManager) :JsonResponse | Response
    {
        $client = $this->getUser();
        $user = $users->findOneBy(['id' => $id,'clients' => $client]);
        if ($user === null) {
            return $this->json(["message" => "Vous n'avez pas d'utilisateurs avec cet identifiant"], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        return new Response(status: 204);

    }


}
