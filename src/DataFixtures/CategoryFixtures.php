<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
         $category = new Category();
         $category->setTitle('cat1');
         $manager->persist($category);

         $category2 = new Category();
         $category2->setTitle('cat2');
         $manager->persist($category2);

         $manager->flush();

         $this->addReference('category1', $category);
         $this->addReference('category2', $category2);
    }
}
