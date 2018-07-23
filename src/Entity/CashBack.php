<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Traits\Column\UuidColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashBack.
 *
 * @ORM\Table(name="cash_back")
 *
 * @ORM\Entity(repositoryClass="App\Repository\CashBackRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CashBack
{
    use UuidColumn;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="title",
     *     type="string",
     *     length=128,
     *     nullable=false
     * )
     */
    private $title;

    /**
     * @ORM\Column(
     *     name="description",
     *     type="text",
     *     length=65535,
     *     nullable=true,
     *     options={
     *         "fixed": false
     *     }
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
     *     name="url",
     *     type="string",
     *     length=255,
     *     nullable=false,
     * )
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(
     *     name="site_url",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "comment": "адрес сайта-заказчика"
     *     }
     * )
     *
     * @var string
     */
    private $siteUrl = '';

    /**
     * @ORM\Column(
     *     name="cash",
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
     *     name="external_id",
     *     type="integer",
     *     nullable=true
     * )
     *
     * @var int
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
     * @var ArrayCollection
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
     *     options={
     *         "comment": "Статус кешбека"
     *     }
     * )
     *
     * @var string
     */
    private $status = CashBackStatusEnumType::STATUS_NOT_PARTNER;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=4,
     *     scale=1,
     *     nullable=false,
     *     options={
     *         "comment": "Рейтинг площадки"
     *     }
     * )
     *
     * @var float
     */
    private $rating = 0;

    /**
     * @var string
     */
    private $trekUrl;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string) $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTrekUrl(): ?string
    {
        return $this->trekUrl;
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function setTrekUrl($url)
    {
        $this->trekUrl = $url;

        return $this;
    }

    /**
     * @return CashBackImage
     */
    public function getCashBackImage(): ?CashBackImage
    {
        return $this->cashBackImage;
    }

    /**
     * @param CashBackImage $cashBackImage
     *
     * @return $this
     */
    public function setCashBackImage(CashBackImage $cashBackImage)
    {
        $this->cashBackImage = $cashBackImage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCash()
    {
        return $this->cash;
    }

    /**
     * @param mixed $cash
     *
     * @return $this
     */
    public function setCash($cash)
    {
        $this->cash = $cash;

        return $this;
    }

    /**
     * @return string
     */
    public function getCondition(): ?string
    {
        return $this->condition;
    }

    /**
     * @param string $condition
     *
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param CashBackCategory $cashBackCategory
     *
     * @return $this
     */
    public function addCategory(CashBackCategory $cashBackCategory)
    {
        if (false === $this->categories->contains($cashBackCategory)) {
            $cashBackCategory->setCashBack($this);
            $this->categories->add($cashBackCategory);
        }

        return $this;
    }

    /**
     * @param CashBackCategory $cashBackCategory
     *
     * @return $this
     */
    public function removeCategory(CashBackCategory $cashBackCategory)
    {
        $this->categories->removeElement($cashBackCategory);

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return int
     */
    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    /**
     * @param int $externalId
     *
     * @return $this
     */
    public function setExternalId(int $externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return CashBackPlatform
     */
    public function getCashBackPlatform(): ?CashBackPlatform
    {
        return $this->cashBackPlatform;
    }

    /**
     * @param CashBackPlatform $cashBackPlatform
     *
     * @return $this
     */
    public function setCashBackPlatform(CashBackPlatform $cashBackPlatform)
    {
        $this->cashBackPlatform = $cashBackPlatform;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return float
     */
    public function getRating(): float
    {
        return (float) $this->rating;
    }

    /**
     * @param float $rating
     *
     * @return $this
     */
    public function setRating(float $rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * @param string $siteUrl
     *
     * @return $this
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;

        return $this;
    }
}
