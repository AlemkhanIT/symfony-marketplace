<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\BasketItem;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BasketController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/basket/add', name: 'app_basket', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $productId = $data['product_id'] ?? null;
        if (!$productId) {
            return $this->json(['error' => 'Product ID is required'], 400);
        }
        $product = $this->em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $user = $this->getUser();
        if(!$user){
            return $this->json(['error'=>'User must be authenticated']);
        }
        $basket = $user->getBasket();
        if (!$basket) {
            $basket = new Basket();
            $basket->setUser($user);
            $this->em->persist($basket);
        }

        $basketItem = new BasketItem();
        $basketItem->setProduct($product);
        $basketItem->setBasket($basket);

        $this->em->persist($basketItem);
        $this->em->flush();
        return $this->json(['message' => 'Product added to basket successfully'], 201);
    }
}
