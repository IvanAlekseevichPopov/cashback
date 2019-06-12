<?php

declare(strict_types=1);

namespace App\Model\Config;

class AdmitadConfig
{
    /**
     * @var string
     */
    private $baseUri = 'https://api.admitad.com';

    /**
     * @var string
     */
    private $authUrl = '/token';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $scope = 'advcampaigns arecords banners websites advcampaigns_for_website manage_advcampaigns statistics deeplink_generator';

    /**
     * @var string
     */
    private $webmasterId;

    public function __construct(string $webmasterId, string $clientId, string $clientSecret)
    {
        $this->webmasterId = $webmasterId;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function setBaseUri(string $baseUri): void
    {
        $this->baseUri = $baseUri;
    }

    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    public function setAuthUrl(string $authUrl): void
    {
        $this->authUrl = $authUrl;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope;
    }

    public function getWebmasterId(): string
    {
        return $this->webmasterId;
    }
}
