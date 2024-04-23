<?php

namespace App\Controller;

use TypeError;
use App\Entity\Users;
use App\Repository\UsersRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;


#[Route('api')]
class UsersController extends AbstractController
{


    #[Route('/users', name: 'app_users', methods:'GET')]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Users::class, groups:['client_user']))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'NOT FOUND',
        )
    ]
    #[OA\Response(
        response: 401,
        description: 'UNAUTHORIZED - Jeton JWT expiré, invalide ou non fournit.',
        )
    ]
    #[OA\Parameter(
        name: 'page',
        example:'2',
        in: 'query',
        description: 'La page de résultat demandé',
        schema: new OA\Schema(type: 'int', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        example:'2',
        in: 'query',
        description: 'Le nombre de résultats souhaités par page',
        schema: new OA\Schema(type: 'int', default: 3)
    )]
    #[OA\Tag(name: 'Users')]
    /**
     * Retrieve all the users linked to a client
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function usersList(UsersRepository $users, TagAwareCacheInterface $cache, #[MapQueryParameter] int $page=0, #[MapQueryParameter] int $limit= 3): JsonResponse
    {
        $client = $this->getUser();
        if (gmp_sign($page) === -1 || gmp_sign($limit) === -1) {
            throw new TypeError("Le numéro de page/limit ne peut être négatif", 404);
        }

        // Considering the "0" value means the first page.
        $page = $page === 0 ? 1 : $page;
        $limit = $limit === 0 ? 3 : $limit;

        // Retrieve the numbers of pages available.
        $pages = (int)(ceil(count($users->findByClients($client)) / $limit));

        if ($page > $pages) {
            throw new HttpException(404, "Cette page n'existe pas");
        }

        $offset = ($page === 1) ? ($page -1) : (($page*$limit)- $limit);
        $key = preg_replace('#@.#','',$client->getUserIdentifier()).'users_'.$page.$limit;
        $userList = $cache->get($key, function (ItemInterface $item) use ($offset, $users, $client, $limit)
        {
            $item->expiresAfter(3600);
            $item->tag('users'.preg_replace('#@.#','',$client->getUserIdentifier()));
            return $users->findByClientsWithPagination($client, $offset, $limit);
        }
        );
        $context = SerializationContext::create()->setGroups(['client_user']);
        return $this->json([$userList, 'page' => $page.'/'.$pages], context: $context);

    }


    #[Route('/users/{id}', name: 'app_user', methods:'GET')]
    #[OA\Response(
        response: 200,
        description: "Retourne le détail de l'utilisateur",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Users::class, groups:['client_user']))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'NOT FOUND',
        )
    ]
    #[OA\Response(
        response: 401,
        description: 'UNAUTHORIZED - Jeton JWT expiré, invalide ou non fournit.',
        )
    ]
    #[OA\Response(
        response: 400,
        description: "Erreur dans la requête"
        )
    ]
    #[OA\Tag(name: 'Users')]
    /**
     * Retrieve a single user
     *
     * @param UsersRepository $users
     * @return JsonResponse
     */
    public function singleUser(UsersRepository $users, int $id, TagAwareCacheInterface $cache): JsonResponse
    {
        $client = $this->getUser();
        $key = preg_replace('#@.#', '', $client->getUserIdentifier()).'singleUser'.$id;
        $user = $cache->get($key, function (ItemInterface $item) use ($users, $id, $client)
            {
                $item->expiresAfter(3600);
                $item->tag('users'.$id);
                return $users->findOneBy(['id' => $id, 'clients' => $client]);
            }
        );
        if ($user === null) {
            return $this->json(["message" => "Vous n'avez pas d'utilisateurs avec cet identifiant"], status: 404);
        }

        $context = SerializationContext::create()->setGroups(['client_user']);
        return $this->json($user, context:$context);

    }


    #[Route('/users', name:'create_user', methods:'POST')]
    #[IsGranted('ROLE_ADMIN', message:"Vous n'avez pas les droits suffisants pour effectuer cet action", statusCode:403)]
    #[OA\Response(
        response: 201,
        description: "Retourne l'utilisateur créé",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Users::class, groups:['client_user']))
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'NOT FOUND',
        )
    ]
    #[OA\Response(
        response: 401,
        description: 'UNAUTHORIZED - Jeton JWT expiré, invalide ou non fournit.',
        )
    ]
    #[OA\Response(
        response: 403,
        description: "Droits insuffisants pour effectuer cet action"
        )
    ]
    #[OA\Response(
        response: 400,
        description: "Erreur dans la requête / body"
        )
    ]
    #[OA\Tag(name: 'Users')]
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
        $client = $this->getUser();
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
        $cache->invalidateTags(['users'.preg_replace('#@.#','',$client->getUserIdentifier())]);
        $context = SerializationContext::create()->setGroups(['client_user']);
        return $this->json([$user], status: 201, context:$context);

    }


    #[Route('/users/{id}', name:'delete_user', methods:'DELETE')]
    #[IsGranted('ROLE_ADMIN', message:"Vous n'avez pas les droits suffisants pour effectuer cet action", statusCode:403)]
    #[OA\Response(
        response: 204,
        description:"L'utilisateur a était supprimé"
    )]
    #[OA\Response(
        response: 404,
        description: 'NOT FOUND',
        )
    ]
    #[OA\Response(
        response: 401,
        description: 'UNAUTHORIZED - Jeton JWT expiré, invalide ou non fournit.',
        )
    ]
    #[OA\Response(
        response: 403,
        description: "Droits insuffisants pour effectuer cet action"
        )
    ]
    #[OA\Response(
        response: 400,
        description: "Erreur dans la requête"
        )
    ]
    #[OA\Tag(name: 'Users')]
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
        $user = $users->findOneBy(['id' => $id, 'clients' => $client]);
        if ($user === null) {
            return $this->json(["message" => "Vous n'avez pas d'utilisateurs avec cet identifiant"], status: 404);
        }
        $entityManager->remove($user);
        $entityManager->flush();
        // Empty the cache.
        $cache->invalidateTags(['users'.$id, 'users'.preg_replace('#@.#','',$client->getUserIdentifier())]);
        return new Response(status: 204);

    }


}
