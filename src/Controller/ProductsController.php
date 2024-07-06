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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        if (!$this->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Access denied. You must be logged in.');
        }
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

    #[Route('/products/edit/{id}', name: 'app_edit')]
    public function edit($id, Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Access denied. You must be logged in.');
        }
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }
        // Ensure the current user is the creator of the product
        $user = $this->security->getUser();
        if ($product->getUserId() !== $user) {
            throw new AccessDeniedException('You do not have permission to edit this product.');
        }
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if($imagePath) {
                if($product->getImagePath() !== null){
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $product->getImagePath()
                    )) {
                        $this->GetParameter('kernel.project_dir') . $product->getImagePath();
                    }
                }
                $newFileName = uniqid() . '.' . $imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $product->setImagePath('/uploads/' . $newFileName);
                $this->em->flush();

                return $this->redirectToRoute('app_products');
            }else{
                $product->setTitle($form->get('title')->getData());
                $product->setPrice($form->get('price')->getData());
                $product->setCategory($form->get('category')->getData());
                $product->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('app_products');
            }
        }
        return $this->render('./products/edit.html.twig',
            [
                'product'=> $product,
                'form' => $form->createView()
            ]);
    }

    #[Route('/products/delete/{id}', name: 'app_delete')]
    public function delete($id): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            throw new AccessDeniedException('Access denied. You must be logged in.');
        }
        $repository = $this->em->getRepository(Product::class);
        $product = $repository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('The product does not exist');
        }
        // Ensure the current user is the creator of the product
        $user = $this->security->getUser();
        if ($product->getUserId() !== $user) {
            throw new AccessDeniedException('You do not have permission to delete this product.');
        }
        $this->em->remove($product);
        $this->em->flush();
        return $this->redirectToRoute('app_products');
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
