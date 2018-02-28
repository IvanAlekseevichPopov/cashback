<?php

declare(strict_types=1);

namespace AppBundle\Entity\Stock;

use AppBundle\Traits\Doctrine\Column\UuidIdColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="cash_back_platform",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "Cashback platforms"
 *     }
 * )
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CashBackPlatform
{
    public const ADMITAD_PLATFORM_ID = '2a10bc8e-2389-c04d-1328-ff9000efd802';

    use UuidIdColumn;

    /**
     * @ORM\Column(
     *     name="title",
     *     type="string",
     *     length=64,
     *     nullable=false,
     *     options={
     *         "fixed": false
     *     }
     * )
     */
    protected $name;

    /**
     * @ORM\Column(
     *     name="base_url",
     *     type="string",
     *     length=128,
     *     nullable=false,
     *     options={
     *         "fixed": false
     *     }
     * )
     */
    protected $baseUrl;

    /**
     * @ORM\Column(
     *     name="client_id",
     *     type="string",
     *     length=64,
     *     nullable=false,
     *     options={
     *         "fixed": false
     *     }
     * )
     */
    protected $clientId;

    /**
     * @ORM\Column(
     *     name="auth_header",
     *     type="string",
     *     length=128,
     *     nullable=false,
     *     options={
     *         "fixed": false
     *     }
     * )
     *
     * @var string
     */
    protected $authHeader;

    /**
     * @ORM\Column(
     *     name="external_platform_id",
     *     type="string",
     *     length=32,
     *     nullable=true,
     *     options={
     *         "fixed": false
     *     }
     * )
     *
     * @var string
     */
    protected $externalPlatformId;

    /**
     * @ORM\Column(
     *     name="token",
     *     type="string",
     *     length=64,
     *     nullable=true,
     *     options={
     *         "fixed": false
     *     }
     * )
     *
     * @var string
     */
    protected $token;

    /**
     * @Doctrine\ORM\Mapping\Column(
     *     name="expired_at",
     *     type="datetime",
     *     nullable=true,
     *     options={
     *         "comment": "Дата протухания токена"
     *     }
     * )
     *
     * @var \DateTime
     */
    protected $expiredAt;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Stock\CashBack",
     *     mappedBy="cashBackPlatform",
     * )
     *
     * @var ArrayCollection
     */
    protected $cashBacks;

    public function __construct()
    {
        $this->cashBacks = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return string
     */
    public function getAuthHeader(): ?string
    {
        return $this->authHeader;
    }

    /**
     * @param string $authHeader
     */
    public function setAuthHeader(string $authHeader)
    {
        $this->authHeader = $authHeader;
    }

    /**
     * @return string
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @return ArrayCollection
     */
    public function getCashBacks(): ArrayCollection
    {
        return $this->cashBacks;
    }

    /**
     * @param CashBack $cashBack
     *
     * @return $this
     */
    public function addCashBack(CashBack $cashBack)
    {
        if (false === $this->cashBacks->contains($cashBack)) {
            $cashBack->setCashBackPlatform($this);
            $this->cashBacks->add($cashBack);
        }

        return $this;
    }

    /**
     * @param CashBack $cashBack
     *
     * @return $this
     */
    public function removeCashBack(CashBack $cashBack)
    {
        $this->cashBacks->removeElement($cashBack);

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(?string $token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt(): ?\DateTime
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return $this
     */
    public function setExpiredAt(?\DateTime $expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getExternalPlatformId(): ?string
    {
        return $this->externalPlatformId;
    }

    /**
     * @param string $externalPlatformId
     *
     * @return $this
     */
    public function setExternalPlatformId(?string $externalPlatformId)
    {
        $this->externalPlatformId = $externalPlatformId;

        return $this;
    }
}
