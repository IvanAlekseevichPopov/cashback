<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public static function getUserAdmin(): User
    {
        $user = new User();

        $user->setPhone('234223423');
        $user->setEmail('admin@gmail.com');
        $user->setUsername('admin');
        $user->setPlainPassword('asdf3423oerjtrretn');
        $user->setSuperAdmin(true);
        $user->setEnabled(true);

        return $user;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $admin = self::getUserAdmin();

        $manager->persist($admin);
        $manager->flush();
    }
}
