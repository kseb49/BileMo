<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

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
    public function productsList(ProductsRepository $productsRepo): JsonResponse
    {
        $products = $productsRepo->findAll();
        return $this->json($products);

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
        if ($product) {
            return $this->json($product);
        }

        return new JsonResponse(status:404);

    }


}
