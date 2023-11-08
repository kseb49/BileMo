<?php

namespace App\Controller;

use TypeError;
use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('api')]
class ProductController extends AbstractController
{

    #[Route('/products', name: 'products', methods:['GET'])]
    /**
     * Get all the products
     *
     * @param ProductsRepository $productsRepo
     * @return JsonResponse
     */
    public function productsList(ProductsRepository $productsRepo, CacheInterface $cache, #[MapQueryParameter] int $page = 0): JsonResponse
    {

        if (gmp_sign($page) === -1) {
            throw new TypeError("Le numéro de page ne peut être négatif", 404);
        }
        // Considering the "0" value means the first page
        $page = $page === 0 ? 1 : $page;

        /**
         * Retrieve the numbers of pages available
         * @var int
         */
        $pages = intval(ceil(count($productsRepo->findAll()) / $productsRepo::RESULT_PER_PAGE));

        if ($page > $pages) {
            throw new HttpException(404, "Cette page n'existe pas");
        }
        $offset = $page === 1 ? $page-1 : ($page*$productsRepo::RESULT_PER_PAGE)-$productsRepo::RESULT_PER_PAGE;
        // $products = $this->caches->cache($offset, Products::class, 'products_list_'.$page);dd($products);
        $products = $cache->get('products_list_'.$page, function(ItemInterface $item) use ($productsRepo, $offset)
            {
                echo ('mise en cache');
                $item->expiresAfter(20);
                return $productsRepo->findWithPagination($offset);
            }
        );
        return $this->json([$products, 'page' => $page.'/'.$pages]);

    }


    #[Route('/products/{id}', name: 'singleProduct', methods:['GET'])]
    /**
     * Get a single product
     *
     * @param Products $product
     * @return JsonResponse
     */
    public function singleProduct(Products $product): JsonResponse
    {
        return $this->json($product);

    }


}
