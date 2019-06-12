<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Entity\CashBack;
use App\Model\Config\AdmitadConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;

class AdmitadApiClient implements ApiClientInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AdmitadAuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var AdmitadConfig
     */
    private $config;

    public function __construct(AdmitadAuthenticatorInterface $authenticator, LoggerInterface $logger, AdmitadConfig $config)
    {
        $this->logger = $logger;
        $this->authenticator = $authenticator;
        $this->config = $config;
    }

//    public function getStreamers(array $parameters = []): array
//    {
//        return $this->getData('/helix/users', $parameters);
//    }
//
//    public function getTopGames(array $parameters = []): array
//    {
//        return $this->getData('/helix/games/top', $parameters);
//    }
//
//    public function getGames(array $parameters = []): array
//    {
//        return $this->getData('/helix/games', $parameters);
//    }
//
//    public function getStreams(array $parameters = []): array
//    {
//        return $this->getData('/helix/streams', $parameters);
//    }
//
//    public function getTags(array $parameters = []): array
//    {
//        return $this->getData('/helix/tags/streams', $parameters);
//    }
//    public function tst($url, $parame)
    public function getCompanyStatus(CashBack $cashBack)
    {
        $this->client->getData(
            $this->config->getBaseUri().
            '/advcampaigns/'.
            $cashBack->getExternalId().
            '/website/'.
            $this->config->getWebmasterId(). '/'.
            $this->authenticator->getToken()
        );

        //TODO check ^

        //$admitadPlatform->getBaseUrl().'advcampaigns/'.$cashBack->getExternalId().'/website/'.$admitadPlatform->getExternalPlatformId().'/', $admitadPlatform->getToken()
    }


    private function getData(string $url, array $parameters = []): array
    {
        $this->logger->info(sprintf('Calling GET %s with %s', $url, json_encode($parameters)));
        try {
            $response = $this->getClient()->request('GET', $url, ['query' => $parameters]);

            return \json_decode((string) $response->getBody(), true, JSON_UNESCAPED_UNICODE);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()) {
                $responseCode = $exception->getResponse()->getStatusCode();
                $responseContent = (string) $exception->getResponse()->getBody();

                $this->logger->error(sprintf(
                    'Bad response exception: %s %s',
                    $responseCode,
                    $responseContent
                ));
            } else {
                $this->logger->error(sprintf(
                    'Bad response exception: %s, no content provided',
                    $exception->getCode()
                ));
            }
        } catch (TransferException $exception) {
            $this->logger->error(sprintf(
                'Transfer exception: %d %s',
                $exception->getCode(),
                $exception->getMessage()
            ));
        }

        return [];
    }

    private function getClient(): Client
    {
        if (null === $this->client || $this->authenticator->isTokenExpired()) {
            $this->client = $this->authenticator->getAuthenticatedClient();
        }

        return $this->client;
    }
}
