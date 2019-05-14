<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="cash_back")
 * @ORM\Entity(repositoryClass="App\Repository\CashBackRepository")
 */
class CashBack
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="string",
     *     length=128,
     * )
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(
     *     type="string",
     *     length=64,
     * )
     */
    private $slug;

    /**
     * @ORM\Column(
     *     type="text",
     *     length=65535,
     *     nullable=true,
     * )
     *
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(
     *     name="cashback_condition",
     *     type="text",
     *     length=65535,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $condition;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=255,
     *     nullable=true
     * )
     *
     * @var string|null
     */
    private $url;

    /**
     * @ORM\Column(
     *     type="string",
     *     length=255,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $siteUrl = '';

    /**
     * @ORM\Column(
     *     type="string",
     *     length=255,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $cash;

    /**
     * @ORM\Column(
     *     type="integer",
     *     nullable=true
     * )
     *
     * @var int?
     */
    private $externalId;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CashBackPlatform",
     *     fetch="LAZY",
     *     inversedBy="cashBacks",
     * )
     * @ORM\JoinColumn(
     *     name="cash_back_platform_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     *
     * @var CashBackPlatform
     */
    private $cashBackPlatform;

    /**
     * @ORM\OneToOne(
     *     targetEntity="CashBackImage",
     *     fetch="EXTRA_LAZY",
     *     cascade={"persist"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(
     *     name="cash_back_image_id",
     *     referencedColumnName="id",
     *     nullable=false
     * )
     *
     * @var CashBackImage
     */
    private $cashBackImage;

    /**
     * @ORM\OneToMany(
     *     targetEntity="CashBackCategory",
     *     mappedBy="cashBack",
     *     orphanRemoval=true,
     *     cascade={"all"},
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @var Collection
     */
    private $categories;

    /**
     * @ORM\Column(
     *     name="active",
     *     type="boolean"
     * )
     *
     * @var bool
     */
    private $active = false;

    /**
     * @ORM\Column(
     *     name="status",
     *     type="CashBackStatusEnumType",
     * )
     *
     * @var string
     */
    private $status = CashBackStatusEnumType::NOT_PARTNER;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=4,
     *     scale=1,
     *     nullable=false,
     * )
     *
     * @var float
     */
    private $rating = 0;

    /**
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="date_immutable")
     */
    private $createdAt;

    /**
     * @var string
     */
    private $trekUrl;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $awaitingTime;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->categories = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getTrekUrl(): ?string
    {
        return $this->trekUrl;
    }

    public function setTrekUrl($url): void
    {
        $this->trekUrl = $url;
    }

    public function getCashBackImage(): ?CashBackImage
    {
        return $this->cashBackImage;
    }

    public function setCashBackImage(CashBackImage $cashBackImage): void
    {
        $this->cashBackImage = $cashBackImage;
    }

    public function getCash(): ?string
    {
        return $this->cash;
    }

    public function setCash(?string $cash): void
    {
        $this->cash = $cash;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): void
    {
        $this->condition = $condition;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(CashBackCategory $cashBackCategory): void
    {
        if (false === $this->categories->contains($cashBackCategory)) {
            $cashBackCategory->setCashBack($this);
            $this->categories->add($cashBackCategory);
        }
    }

    /**
     * @param CashBackCategory $cashBackCategory
     *
     * @return $this
     */
    public function removeCategory(CashBackCategory $cashBackCategory)
    {
        $this->categories->removeElement($cashBackCategory);
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getCashBackPlatform(): ?CashBackPlatform
    {
        return $this->cashBackPlatform;
    }

    public function setCashBackPlatform(CashBackPlatform $cashBackPlatform): void
    {
        $this->cashBackPlatform = $cashBackPlatform;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getRating(): float
    {
        return (float) $this->rating;
    }

    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    public function setSiteUrl($siteUrl): void
    {
        $this->siteUrl = $siteUrl;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getAwaitingTime(): ?int
    {
        return $this->awaitingTime;
    }

    public function setAwaitingTime(?int $awaitingTime): void
    {
        $this->awaitingTime = $awaitingTime;
    }
}
