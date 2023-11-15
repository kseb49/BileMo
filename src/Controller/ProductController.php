<?php

namespace App\Controller;

use App\Entity\Products;
use TypeError;
use App\Repository\ProductsRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('api')]
class ProductController extends AbstractController
{

    #[Route('/products', name: 'products', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits de la page demandée',
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
        example:'1',
        in: 'query',
        description: 'La page de résultat demandé',
        schema: new OA\Schema(type: 'int', default: 1)
    )]
    #[OA\Tag(name: 'Products')]
    /**
     * Get all the products
     *
     * @param integer $page The requested page
     * @param CacheInterface $cache
     * @param ProductsRepository $productsRepo
     * @return JsonResponse
     */
    public function productsList(ProductsRepository $productsRepo, CacheInterface $cache, #[MapQueryParameter] int $page= 0): JsonResponse
    {

        if (gmp_sign($page) === -1) {
            throw new TypeError("Le numéro de page ne peut être négatif", 404);
        }
        // Considering the "0" value means the first page.
        $page = $page === 0 ? 1 : $page;

        // Retrieve the numbers of pages available.
        $pages = (int)(ceil(count($productsRepo->findAll()) / $productsRepo::RESULT_PER_PAGE));

        if ($page > $pages) {
            throw new HttpException(404, "Cette page n'existe pas");
        }
        $offset = ($page === 1) ? ($page -1) : ($page*$productsRepo::RESULT_PER_PAGE)-$productsRepo::RESULT_PER_PAGE;
        // $products = $this->caches->cache($offset, Products::class, 'products_list_'.$page);dd($products);
        $products = $cache->get('products_list_'.$page, function(ItemInterface $item) use ($productsRepo, $offset)
            {
                $item->expiresAfter(20);
                return $productsRepo->findWithPagination($offset);
            }
        );
        return $this->json([$products, 'page' => $page.'/'.$pages]);

    }


    #[Route('/products/{id}', name: 'singleProduct', methods:['GET'])]
    #[OA\Response(
        response: 200,
        description: "Détail d'un produit",
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Products::class))
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
    // #[OA\Parameter(
    //     name: 'id',
    //     in: 'path',
    //     required:true,
    //     description: "L'identifiant du produit",
    //     schema: new OA\Schema(type: 'int')
    // )]
    #[OA\Tag(name: 'Products')]
    /**
     * Get a single product
     *
     * @param CacheInterface $cache
     * @param integer $id
     * @param ProductsRepository $productsRepo
     * @return JsonResponse
     */
    public function singleProduct(CacheInterface $cache, int $id, ProductsRepository $productsRepo): JsonResponse
    {
        $product = $cache->get('product'.$id, function(ItemInterface $item) use ($productsRepo, $id)
        {
            $item->expiresAfter(1000);
            return $productsRepo->findOneById($id);
        }
        
        );
        if($product === null) {
            throw new HttpException(404, "Ce produit n'existe pas");
        }

        return $this->json($product);

    }


}
