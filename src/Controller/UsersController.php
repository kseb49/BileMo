<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function usersList(UsersRepository $users): JsonResponse
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
    #[IsGranted('ROLE_ADMIN', message:"Vous n'avez pas les droits suffisants pour effectuer cet action", statusCode:403)]
    /**
     * Create an user
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function createUser(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer) :JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), Users::class, 'json');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages =[];
            foreach ($errors as $key => $value) {
                $messages[] = ["Erreur {$key}" => $errors->get($key)->getMessage()];
            }
            return new JsonResponse($messages, 400, [], false);
        }
        // Look for a duplicate user
        $checkForADuplicate = $entityManager->getRepository(Users::class)->findOneBy(['email' => $user->getEmail(), 'clients' => $this->getUser()]);
        if ($checkForADuplicate !== null) {
            throw new httpException(400, "Un utilisateur avec cet email existe déjà");
        }

        $user->setClients($this->getUser());
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json([$user], 201, context:['groups' => 'client_user']);

    }


    #[Route('/users/{id}', name:'delete_user', methods:'DELETE')]
    #[IsGranted('ROLE_ADMIN', message:"Vous n'avez pas les droits suffisants pour effectuer cet action", statusCode:403)]
    /**
     * Delete an user
     *
     * @param integer $id the user unique identifier
     * @param UsersRepository $users
     * @param EntityManagerInterface $entityManager
     * @return void
     */
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
