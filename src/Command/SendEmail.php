<?php

declare(strict_types=1);

namespace App\Command;

use App\DBAL\Types\Enum\TransactionEnumType;
use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackPlatform;
use App\Entity\CashBackTrek;
use App\Entity\Transaction;
use App\Manager\TransactionManager;
use App\Repository\CashBackRepository;
use App\Service\AdmitadApiHandler;
use Doctrine\ORM\EntityManagerInterface;
use Mailgun\Mailgun;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SendEmail.
 */
class SendEmail extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:send')
            ->setDescription('sends email');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START');

        $mgClient = new Mailgun('ae06c3a8d6d17bdb53f8d428769604b1-a4502f89-3beb2a14');
        $domain = "cashtan.tk";

        $result = $mgClient->sendMessage($domain, array(
            'from'    => 'Excited User <mailgun@cashtan.tk>',
            'to'      => 'tirblabla@gmail.com',
            'subject' => 'Hello',
            'text'    => 'Testing some Mailgun awesomness!'
        ));

        $output->writeln('STOP');
    }
}
