<?php

namespace App\Controller;

// use App\Entity\Clients;
use App\Repository\ClientsRepository;
use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
        $client = $clients->findOneBy(['id' => 46]);
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
    public function singleUser(UsersRepository $users, ClientsRepository $clients, int $id): JsonResponse
    {
        $client = $clients->findOneBy(['id' => 46]);
        $user = $users->findOneBy(['id' => $id]);
        if ($user->getClients() !== $client) {
            return $this->json(["message" => "Vous n'avez pas de client avec cet identifiant"], 404);
        }
        return $this->json($user, context:['groups' => 'client_user']);

    }


}
