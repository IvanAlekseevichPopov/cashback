<?php

declare(strict_types = 1);

namespace App\Command;

use GuzzleHttp\Client;
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

//        $mgClient = new Mailgun('ae06c3a8d6d17bdb53f8d428769604b1-a4502f89-3beb2a14');
//        $domain = "cashtan.tk";
//        $result = $mgClient->sendMessage($domain, array(
//            'from'    => 'Excited User <mailgun@cashtan.tk>',
//            'to'      => 'tirblabla@gmail.com',
//            'subject' => 'Hello',
//            'text'    => 'Testing some Mailgun awesomness!'
//        ));


        $client = new Client(['base_uri' => 'https://api.mailgun.net']);
//
        $res = $client->request('POST', '/v3/cashtan.tk/messages', [
            'auth' => [
                'api',
                'ae06c3a8d6d17bdb53f8d428769604b1-a4502f89-3beb2a14',
            ],
            'multipart' => [
                [
                    'name' => 'from',
                    'contents' => 'Excited User <mailgun@cashtan.tk>',
                ],
                [
                    'name' => 'to',
                    'contents' => 'tirblabla@gmail.com',
                ],
                [
                    'name' => 'subject',
                    'contents' => 'Hello',
                ],
                [
                    'name' => 'text',
                    'contents' => '2Testing some Mailgun awesomeness2',
                ],
            ],
        ]);

        $output->writeln($res->getBody()->getContents());

        $output->writeln('STOP');
    }
}
