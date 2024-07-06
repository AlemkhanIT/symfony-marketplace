<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    private $em;
    private $security;

    public function __construct(EntityManagerInterface $em, Security $security){
        $this->em = $em;
        $this->security = $security;
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
        // Set the user for the product
        $newProduct = new Product();
        $form = $this->createForm(ProductFormType::class, $newProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newProduct = $form->getData();
            $imagePath = $form->get('imagePath')->getData();
            $user = $this->security->getUser();
            $newProduct->setUserId($user);

            if ($imagePath) {
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $newProduct->setImagePath('/uploads/' . $newFileName);
            }

            $this->em->persist($newProduct);
            $this->em->flush();
            return $this->redirectToRoute('app_products');
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
