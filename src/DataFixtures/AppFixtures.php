<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user = new Users();
        $user->setEmail('commonUser@gmail.com');
        $user->setPassword($this->passwordHasher->hashPassword($user,'12345'));
        $user->setRoles(['ROLE_USER']);
        $user->setAvatar('');
        $user->setGender('men');
        $user->setName('Kocmo');
        $manager->persist($user);
        $manager->flush();
    }
}
