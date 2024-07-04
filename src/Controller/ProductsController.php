<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;

    }
    #[Route(path: '/products', name: 'app_products')]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Product::class);
        $products = $repository->findAll();
        return $this->render('./products/index.html.twig', [
            'products' => $products
        ]);
    }
    #[Route('/products/create', name: 'app_create')]
    public function create(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($product);
            $this->em->flush();
        }
        return $this->render('./products/create.html.twig', ['form' => $form->createView()]);
    }
    #[Route(path: '/products/{id}', name: 'app_product')]
    public function show($id): Response
    {
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);
        return $this->render('./products/more.html.twig', [
            'product' => $product
        ]);
    }
}
