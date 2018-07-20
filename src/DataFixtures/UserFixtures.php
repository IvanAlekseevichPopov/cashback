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
        $user = new User();

        $user
            ->setPhone('234223423')
            ->setEmail('asdf@adsf')
            ->setUsername('testUserName')
            ->setPassword('asdfasdfasdf')
            ->setEnabled(true);

        $manager->persist($user);
        $manager->flush();
    }
}
