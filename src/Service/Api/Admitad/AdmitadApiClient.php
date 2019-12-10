<?php

declare(strict_types=1);

namespace App\Service\Api\Admitad;

use App\Entity\CashBack;
use App\Model\Config\AdmitadConfig;
use App\Service\Api\ApiAuthenticatorInterface;
use App\Service\Api\CashbackApiClientInterface;
use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use Psr\Log\LoggerInterface;
use function sprintf;

class AdmitadApiClient implements CashbackApiClientInterface
{
    private const OLDEST_PAYMENT_CHECK = '-70 days';
    private const NEWEST_PAYMENT_CHECK = '-2 days';
    private const DATE_FORMAT = 'd.m.Y';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $apiLogger;

    /**
     * @var ApiAuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var AdmitadConfig
     */
    private $config;

    public function __construct(ApiAuthenticatorInterface $authenticator, LoggerInterface $apiLogger, AdmitadConfig $config)
    {
        $this->apiLogger = $apiLogger;
        $this->authenticator = $authenticator;
        $this->config = $config;
    }

    private static function getStartDate(): string
    {
        return (new DateTimeImmutable(self::OLDEST_PAYMENT_CHECK))->format(self::DATE_FORMAT);
    }

    private static function getFinishDate(): string
    {
        return (new DateTimeImmutable(self::NEWEST_PAYMENT_CHECK))->format(self::DATE_FORMAT);
    }

    public function getPayments(int $offset, int $limit): array
    {
        return $this->getData(
            sprintf('%s/statistics/sub_ids/', $this->config->getBaseUri()),
            [
                'limit' => $limit,
                'offset' => $offset,
                'date_start' => self::getStartDate(),
                'date_end' => self::getFinishDate(),
            ]
        );
    }

    public function getCompanyStatus(CashBack $cashBack): array
    {
        return $this->getData(
            sprintf('%s/advcampaigns/%s/website/%s/',
                $this->config->getBaseUri(),
                $cashBack->getExternalId(),
                $this->config->getWebmasterId()
            )
        );
    }

    /**
     * Returns companies available in admitad.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $extraParams
     *
     * @return array
     */
    public function getCampaigns(
        int $offset,
        int $limit,
        array $extraParams = [
            'has_tool' => 'deeplink', //related to cashback type
            'traffic_id' => 1, //Only cashback type of traffic]): array
        ]
    ): array {
        $params = array_merge(
            $extraParams,
            [
                'offset' => $offset,
                'limit' => $limit,
            ]
        );

        return $this->getData(
            sprintf('%s/advcampaigns/', $this->config->getBaseUri()),
            $params
        );
    }

    public function getTrafficTypes(): array
    {
        return $this->getData(
            sprintf('%s/traffic/', $this->config->getBaseUri())
        );
    }

    public function requestPartnership(CashBack $cashBack): array
    {
        return $this->getData(
            sprintf(
                '%s/advcampaigns/%d/attach/%d/',
                $this->config->getBaseUri(),
                $cashBack->getExternalId(),
                $this->config->getWebmasterId()
            ),
            [],
            'POST'
        );
    }

    private function getData(string $url, array $parameters = [], string $method = 'GET'): array
    {
        $this->apiLogger->info(sprintf('Calling %s %s with %s', $method, $url, json_encode($parameters)));

        try {
            $response = $this->getClient()->request($method, $url, ['query' => $parameters]);

            return \json_decode((string) $response->getBody(), true, JSON_UNESCAPED_UNICODE);
        } catch (BadResponseException $exception) {
            if ($exception->getResponse()) {
                $responseCode = $exception->getResponse()->getStatusCode();
                $responseContent = (string) $exception->getResponse()->getBody();

                $this->apiLogger->error(sprintf(
                    'Bad response exception: %s %s',
                    $responseCode,
                    $responseContent
                ));
            } else {
                $this->apiLogger->error(sprintf(
                    'Bad response exception: %s, no content provided',
                    $exception->getCode()
                ));
            }
        } catch (TransferException $exception) {
            $this->apiLogger->error(sprintf(
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
