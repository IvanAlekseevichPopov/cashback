<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserFixtures.
 */
class UserFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $admin = self::getUserAdmin();
        $ivan = new User();
        $ivan
            ->setPhone('89816988477')
            ->setEmail('ivan.alekseevich.popov@gmail.com')
            ->setUsername('Ivan Popov')
            ->setPassword('$2y$13$h1PPDG8xcKntdGnHMNrZdOgAqK.B6EqYbsjHAW6HhKuhJvzM/Z2Mu')
            ->setSuperAdmin(true)
            ->setEnabled(true);

        $manager->persist($admin);
        $manager->persist($ivan);
        $manager->flush();
    }

    /**
     * Returns admin user for tests.
     *
     * @return User
     */
    public static function getUserAdmin(): User
    {
        $user = new User();

        $user
            ->setPhone('234223423')
            ->setEmail('admin@test_mail.com')
            ->setUsername('admin')
            ->setPlainPassword('asdf3423oerjtrretn')
            ->setSuperAdmin(true)
            ->setEnabled(true);

        return $user;
    }
}
