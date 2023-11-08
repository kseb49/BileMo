<?php

namespace App\Controller;

use TypeError;
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
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
    public function usersList(UsersRepository $users, TagAwareCacheInterface $cache, #[MapQueryParameter] int $page= 0): JsonResponse
    {
        $client = $this->getUser();
        if (gmp_sign($page) === -1) {
            throw new TypeError("Le numéro de page ne peut être négatif", 404);
        }

        // Considering the "0" value means the first page.
        $page = $page === 0 ? 1 : $page;

        // Retrieve the numbers of pages available.
        $pages = (int)(ceil(count($users->findByClients($client)) / $users::RESULT_PER_PAGE));

        if ($page > $pages) {
            throw new HttpException(404, "Cette page n'existe pas");
        }

        $offset = ($page === 1) ? ($page -1) : ($page*$users::RESULT_PER_PAGE)-$users::RESULT_PER_PAGE;
        $userList = $cache->get('users_'.$page, function(ItemInterface $item) use($offset, $users, $client)
        {
            echo('mise en cache');
            $item->expiresAfter(10000);
            $item->tag('users');
            return $users->findByClientsWithPagination($client, $offset);
        });

        return $this->json([$userList, 'page' => $page.'/'.$pages], context:['groups' => 'client_user']);

    }


    #[Route('/users/{id}', name: 'app_user', methods:'GET')]
    /**
     * Retrieve a single user
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function singleUser(UsersRepository $users, int $id, TagAwareCacheInterface $cache): JsonResponse
    {
        $client = $this->getUser();
        $user = $cache->get('singleUser'.$id, function(ItemInterface $item) use($users, $id, $client)
        {
            echo('mise en cache');
            $item->expiresAfter(1000);
            $item->tag('users');
            return $users->findOneBy(['id' => $id, 'clients' => $client]);
        });
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
     * @param Request $request Http request
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function createUser(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, SerializerInterface $serializer, TagAwareCacheInterface $cache) :JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), Users::class, 'json');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $value) {
                $messages[] = [$value->getPropertyPath() => $value->getMessage()];
            }
            return new JsonResponse($messages, 400, [], false);
        }
        // Look for a duplicate user.
        $checkForADuplicate = $entityManager->getRepository(Users::class)->findOneBy(['email' => $user->getEmail(), 'clients' => $this->getUser()]);
        if ($checkForADuplicate !== null) {
            throw new httpException(400, "Un utilisateur avec cet email existe déjà");
        }

        $user->setClients($this->getUser());
        $entityManager->persist($user);
        $entityManager->flush();
        // Empty the cache.
        $cache->invalidateTags(['users']);
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
    public function deleteUser(int $id, UsersRepository $users, EntityManagerInterface $entityManager, TagAwareCacheInterface $cache) :JsonResponse | Response
    {
        $client = $this->getUser();
        $user = $users->findOneBy(['id' => $id,'clients' => $client]);
        if ($user === null) {
            return $this->json(["message" => "Vous n'avez pas d'utilisateurs avec cet identifiant"], 404);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        // Empty the cache.
        $cache->invalidateTags(['users']);
        return new Response(status: 204);

    }


}
