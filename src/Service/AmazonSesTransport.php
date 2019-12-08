<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use SimpleEmailService;
use SimpleEmailServiceMessage;
use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage;
use Swift_Transport;

class AmazonSesTransport implements Swift_Transport
{
    /** @var LoggerInterface */
    private $logger;

    /** @var string */
    private $privateKey;

    /** @var string */
    private $publicKey;

    /**
     * MailgunTransport constructor.
     *
     * @param string          $publicKey
     * @param string          $privateKey
     * @param LoggerInterface $logger
     */
    public function __construct(string $publicKey, string $privateKey, LoggerInterface $logger)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->logger = $logger;
    }

    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
        // TODO: Implement isStarted() method.
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
        // TODO: Implement start() method.
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
        // TODO: Implement stop() method.
    }

    /**
     * Check if this Transport mechanism is alive.
     *
     * If a Transport mechanism session is no longer functional, the method
     * returns FALSE. It is the responsibility of the developer to handle this
     * case and restart the Transport mechanism manually.
     *
     * @example
     *
     *   if (!$transport->ping()) {
     *      $transport->stop();
     *      $transport->start();
     *   }
     *
     * The Transport mechanism will be started, if it is not already.
     *
     * It is undefined if the Transport mechanism attempts to restart as long as
     * the return value reflects whether the mechanism is now functional.
     *
     * @return bool TRUE if the transport is alive
     */
    public function ping()
    {
        return true;
        // TODO: Implement ping() method.
    }

    /**
     * Send the given Message.
     *
     * Recipient/sender data will be retrieved from the Message API.
     * The return value is the number of recipients who were accepted for delivery.
     *
     * @param Swift_Mime_SimpleMessage $message
     * @param string[]                 $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $m = new SimpleEmailServiceMessage();
        $m->addTo($this->genTo($message->getTo()));
        $m->setFrom($this->genFrom($message->getFrom()));
        $m->setSubject($message->getSubject());
        $m->setMessageFromString($message->getBody());

        $ses = new SimpleEmailService($this->publicKey, $this->privateKey, SimpleEmailService::AWS_EU_WEST1);

        $response = $ses->sendEmail($m);
        $this->logger->info('Sent new email', $response);

        return count($message->getTo());
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        // TODO: Implement registerPlugin() method.
    }

    /**
     * @param array $from
     *
     * @return string
     */
    private function genFrom(array $from): string
    {
        $fromRow = '';
        foreach ($from as $mail => $name) {
            $fromRow .= \sprintf('%s <%s> ', $name, $mail);
        }

        return $fromRow;
    }

    /**
     * @param array $to
     *
     * @return string
     */
    private function genTo(array $to): string
    {
        $toRow = '';
        foreach ($to as $mail => $name) {
            $toRow .= \sprintf('%s ', $mail);
        }

        return $toRow;
    }
}
