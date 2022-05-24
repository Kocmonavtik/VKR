<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user= new Users();
        $user->setEmail('commonUser@gmail.com');
        $user->setPassword('12345');
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar('');
        $user->setGender('men');
        $user->setName('Kocmo');
        $manager->persist($user);
        $manager->flush();
    }
}
