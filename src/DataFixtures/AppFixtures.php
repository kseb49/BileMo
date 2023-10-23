<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Clients;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {

    }

    /**
     * Crete 15 clients
     *
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        for ($i=0; $i < 15; $i++) {
            $client = new Clients();
            $client->setEmail($faker->safeEmail())
            ->setPassword($this->passwordHasher->hashPassword($client, '123456'));
            if($i % 2 === 1) {
                $client->setRoles(['ROLE_ADMIN']);
            }
            $manager->persist($client);
        }

        $manager->flush();
    }


}
