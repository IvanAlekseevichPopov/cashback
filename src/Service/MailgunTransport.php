<?php

declare(strict_types = 1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Symfony\Component\HttpFoundation\Response;

/**
 * MailgunTransport
 */
class MailgunTransport implements Swift_Transport
{
    const BASE_URI    = 'https://api.mailgun.net';
    const API_VERSION = 3;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $domain;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * MailgunTransport constructor.
     *
     * @param string          $apiKey
     * @param string          $domain
     * @param LoggerInterface $logger
     */
    public function __construct(string $apiKey, string $domain, LoggerInterface $logger)
    {
        $this->apiKey = $apiKey;
        $this->domain = $domain;
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
        try {
            $client = new Client(['base_uri' => self::BASE_URI]);
            $response = $client->request('POST', $this->genUrl(), [
                'auth' => ['api', $this->apiKey,],
                'multipart' => [
                    [
                        'name' => 'from',
                        'contents' => $this->genFrom($message->getFrom()),
                    ],
                    [
                        'name' => 'to',
                        'contents' => $this->genTo($message->getTo()),
                    ],
                    [
                        'name' => 'subject',
                        'contents' => $message->getSubject(),
                    ],
                    [
                        'name' => 'html',
                        'contents' => $message->getBody(),
                    ],
                ],
            ]);
            if (Response::HTTP_OK !== $response->getStatusCode()) {
                $this->logger->alert('Can not send email', $response->getBody()->getContents());
            }
            $sent = count($message->getTo());
        } catch (ClientException $exception) {
            $this->logger->alert($exception->getMessage());
            $sent = 0;
        }

        return $sent;
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
     * @return string
     */
    private function genUrl(): string
    {
        return \sprintf('/v%d/%s/messages', self::API_VERSION, $this->domain);
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
