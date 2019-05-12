<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use DateTimeImmutable;
use Deployer\Collection\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cash_back_platform")
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CashBackPlatform
{
    public const ADMITAD_PLATFORM_ID = 1;

    use IntegerAutoIncrementIdColumn;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=64,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=128,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $baseUrl;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=64,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $clientId;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=128,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $authHeader;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=32,
     *     nullable=true,
     * )
     *
     * @var string
     */
    private $externalPlatformId;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=64,
     *     nullable=true,
     * )
     *
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(
     *     type="datetime_immutable",
     *     nullable=true,
     * )
     *
     * @var DateTimeImmutable
     */
    private $expiredAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CashBack",
     *     mappedBy="cashBackPlatform",
     * )
     *
     * @var Collection|Cashback[]
     */
    private $cashBacks;

    public function __construct()
    {
        $this->cashBacks = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getAuthHeader(): ?string
    {
        return $this->authHeader;
    }

    public function setAuthHeader(string $authHeader): void
    {
        $this->authHeader = $authHeader;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }

    public function getCashBacks(): Collection
    {
        return $this->cashBacks;
    }

    public function addCashBack(CashBack $cashBack): void
    {
        if (false === $this->cashBacks->contains($cashBack)) {
            $cashBack->setCashBackPlatform($this);
            $this->cashBacks->add($cashBack);
        }
    }

    public function removeCashBack(CashBack $cashBack): void
    {
        $this->cashBacks->removeElement($cashBack);
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }

    public function getExpiredAt(): ?DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?DateTimeImmutable $expiredAt): void
    {
        $this->expiredAt = $expiredAt;
    }

    public function getExternalPlatformId(): ?string
    {
        return $this->externalPlatformId;
    }

    public function setExternalPlatformId(?string $externalPlatformId): void
    {
        $this->externalPlatformId = $externalPlatformId;
    }
}
