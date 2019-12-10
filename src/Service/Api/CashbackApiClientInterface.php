<?php

declare(strict_types=1);

namespace App\Service\Api;

use App\Entity\CashBack;

interface CashbackApiClientInterface
{
    public function getCompanyStatus(CashBack $cashBack): array;

    public function getCampaigns(int $offset, int $limit, array $extraParams = []): array;

    public function getPayments(int $offset, int $limit): array;

    public function requestPartnership(CashBack $cashBack): array;

    public function getTrafficTypes(): array;
}
