<?php

namespace App\Tests\Functional\Command;

use App\Entity\News\NewsEntry;
use App\Entity\User;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends KernelTestCase
{
    /**
     * @test
     */
    public function createUserFosCommand(): void
    {

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $input = new StringInput('fos:user:create --env=test');
//        $output = new StringOutput();
        $input->setArgument('username', 'asdfadsfasdf');
        $input->setArgument('email', 'asdfasdf@asdfadsf.ru');
        $input->setArgument('password', 'gasfdhasdgdsdf');

        $application->run($input);

        $this->assertUserCreated($kernel);
    }

    /**
     * @param Kernel $kernel
     */
    private function assertUserCreated(Kernel $kernel): void
    {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => 'asdgasdasd']);

        dump($user);
    }
}
