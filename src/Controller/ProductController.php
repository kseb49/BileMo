<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api')]
class ProductController extends AbstractController
{


    #[Route('/products', name: 'products', methods:['GET'])]
    public function productsList(ProductsRepository $productsRepo): JsonResponse
    {
        $products = $productsRepo->findAll();
        return $this->json($products);
    }


    #[Route('/products/{id}', name: 'singleProduct', methods:['GET'])]
    public function singleProduct(Products $product): JsonResponse
    {
       
            if ($product) {
                return $this->json($product);
            }
        
            return new JsonResponse(status:404);
      
    }
}
