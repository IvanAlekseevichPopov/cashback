<?php

declare(strict_types=1);

namespace App\Service\Api\Admitad;

use App\Model\Config\AdmitadConfig;
use App\Service\Api\ApiAuthenticatorInterface;
use Doctrine\Common\Cache\PhpFileCache;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ApiAuthenticator implements ApiAuthenticatorInterface
{
    private const TOKEN_VAULT_KEY = 'admitad.bearer_token';

    /**
     * @var PhpFileCache
     */
    private $fileCache;

    /**
     * @var AdmitadConfig
     */
    private $config;

    public function __construct(PhpFileCache $fileCache, AdmitadConfig $config)
    {
        $this->fileCache = $fileCache;
        $this->config = $config;
    }

    public function getAuthenticatedClient(): ClientInterface
    {
        $token = $this->fileCache->fetch(self::TOKEN_VAULT_KEY);
        if (false === $token) {
            $token = $this->updateToken();
        }

        return new Client([
            'headers' => [
                'Authorization' => "Bearer $token",
            ],
            'base_uri' => $this->config->getBaseUri(),
        ]);
    }

    public function isTokenExpired(): bool
    {
        return !$this->fileCache->contains(self::TOKEN_VAULT_KEY);
    }

    public function getToken(): string
    {
        if ($this->isTokenExpired()) {
            return $this->updateToken();
        }

        return (string) $this->fileCache->fetch(self::TOKEN_VAULT_KEY);
    }

    private function updateToken(): string
    {
        $client = new Client(['base_uri' => $this->config->getBaseUri()]);
        $response = $client->request('POST', $this->config->getAuthUrl(), [
            'auth' => [
                $this->config->getClientId(),
                $this->config->getClientSecret(),
            ],
            'query' => http_build_query([
                'grant_type' => 'client_credentials',
                'client_id' => $this->config->getClientId(),
                'scope' => $this->config->getScope(),
            ]),
        ]);

        $response = \json_decode((string) $response->getBody(), true, JSON_UNESCAPED_UNICODE);
        $token = $response['access_token'];

        $this->fileCache->save(self::TOKEN_VAULT_KEY, $token, $response['expires_in'] - 10);

        return $token;
    }
}
