<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Import the hasher

class AppFixtures extends Fixture
{
    private $passwordHasher;

    // Use the constructor to automatically get the hasher service
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Create the new User entity
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        // 2. Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'password' // The plain text password you want to use
        );
        $user->setPassword($hashedPassword);

        // 3. Persist the user
        $manager->persist($user);

        // 4. Flush to save to the database
        $manager->flush();
    }
}