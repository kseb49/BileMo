<?php

namespace App\DataFixtures;

use App\Entity\Clients;
use Faker\Factory;
use App\Entity\Users;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class UsersCreateFixtures extends Fixture
{

    /**
     * Crete 50 users
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        $clients = $manager->getRepository(Clients::class)->findAll();
        foreach ($clients as $value) {
            $ids[] = $value->getId();
        }
        $faker = Factory::create('fr_FR');
        for ($i=0; $i < 50; $i++) {
            $client = $manager->getRepository(Clients::class)->findOneById($faker->randomElement($ids));
            $user = new Users();
            $user->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail($faker->safeEmail())
            ->setClients($client);
            $manager->persist($user);
        }

        $manager->flush();
    }


}
