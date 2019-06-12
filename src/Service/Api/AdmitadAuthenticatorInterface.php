<?php

declare(strict_types=1);

namespace App\Service\Api;

use GuzzleHttp\ClientInterface;

interface AdmitadAuthenticatorInterface
{
    public function getAuthenticatedClient(): ClientInterface;

    public function isTokenExpired(): bool;

    public function getToken(): string;
}
