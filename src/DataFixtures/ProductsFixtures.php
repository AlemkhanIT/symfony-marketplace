<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
         $product = new Product();
         $user = new User();
         $user->setEmail("hi@mail.com");
         $user->setPassword("123");
         $product->setTitle('Hello');
         $product->setDescription('World');
         $product->setPrice(1000);
         $product->setImagePath('https://letsenhance.io/static/8f5e523ee6b2479e26ecc91b9c25261e/1015f/MainAfter.jpg');
         $product->setCategory($this->getReference('category1'));
         $product->setUserId($this->getReference('user'));

         $manager->persist($product);

        $product2 = new Product();
        $product2->setTitle('Hello2');
        $product2->setDescription('World2');
        $product2->setPrice(1002);
        $product2->setImagePath('https://www.simplilearn.com/ice9/free_resources_article_thumb/what_is_image_Processing.jpg');
        $product2->setCategory($this->getReference('category1'));
        $product2->setUserId($this->getReference('user'));
        $manager->persist($product2);

        $product3 = new Product();
        $product3->setTitle('World');
        $product3->setDescription('Hellp');
        $product3->setPrice(1);
        $product3->setImagePath('https://letsenhance.io/static/8f5e523ee6b2479e26ecc91b9c25261e/1015f/MainAfter.jpg');
        $product3->setCategory($this->getReference('category2'));
        $product3->setUserId($this->getReference('user'));
        $manager->persist($product3);

         $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
