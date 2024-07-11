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

    #[Route('/basket', name: 'get_basket', methods: ['GET'])]
    public function getBasket(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(["message" => "User must be authenticated"], 401);
        }

        $basket = $user->getBasket();
        $items = $basket ? $basket->getBasketItems()->toArray() : [];

        return $this->render('basket/index.html.twig', [
            'items' => $items,
        ]);
    }
    #[Route('/basket/add', name: 'app_basketAdd', methods: ['POST'])]
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
            return $this->json(['error'=>'User must be authenticated'], 401);
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

    #[Route('/basket/remove/{id}', name: 'rm_basketItem')]
    public function remove($id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], 401);
        }

        $basketItem = $this->em->getRepository(BasketItem::class)->find($id);

        if (!$basketItem) {
            return $this->json(['error' => 'Basket item not found'], 404);
        }

        if ($basketItem->getBasket()->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $this->em->remove($basketItem);
        $this->em->flush();

        return $this->json(['message' => 'Item removed from basket successfully']);
    }
}
