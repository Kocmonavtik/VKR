<?php

namespace App\DataFixtures;

use App\Entity\SourceGoods;
use App\Entity\Store;
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
        $user->setEmail('yandexShop@yandex.ru');
        $user->setPassword($this->passwordHasher->hashPassword($user, '12345'));
        $user->setRoles(['ROLE_CLIENT']);
        $user->setAvatar('');
        $user->setGender('male');
        $user->setName('Kocmo');
        $manager->persist($user);
        $manager->flush();

        $store = new Store();
        $store->setCustomer($user);
        $store->setNameStore('Yandex market');
        $store->setDescription('Крупнейших в России макретплейс');
        $store->setLogo();
        $store->setUrlStore('https://market.yandex.ru/');
        $manager->persist($store);
        $manager->flush();

        $sourceGoods = new SourceGoods();
        $sourceGoods->setCustomer($user);
        $sourceGoods->setStore($store);
        $sourceGoods->setUrl();
        $manager->persist($sourceGoods);
        $manager->flush();
    }
}
