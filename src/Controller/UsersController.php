<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class UsersController extends AbstractController
{


    #[Route('/users', name: 'app_users', methods:'GET')]
    /**
     * Retrieve all the users
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function usersList(UsersRepository $users): JsonResponse
    {
        if ($this->getUser()) {
            $id = $this->getUser()->getUserIdentifier();
        }
        $userList = $users->findBy($id);
        return $this->json($userList);

    }


}
