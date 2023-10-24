<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Products;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductsCreateFixtures extends Fixture
{

    /**
     * Create 100 products
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $brands =
        [
            "samsung",
            "sony",
            "apple",
            "xiaomi",
            "one +",
            "nokia",
            "huawei",
            "lg",
        ];
        $faker = Factory::create('fr_FR');
        for ($i=0; $i < 100; $i++) {
            $product = new Products();
            $product->setName($faker->word())
            ->setBrand($faker->randomElement($brands))
            ->setStock($faker->randomNumber(2, false))
            ->setDescription($faker->paragraphs(4, true))
            ->setPrice($faker->numberBetween(500, 1500));
            $manager->persist($product);
        }

        $manager->flush();

    }


}
