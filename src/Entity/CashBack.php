<?php

declare(strict_types=1);

namespace AppBundle\Entity\Stock;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBackCategory;
use App\Entity\CashBackImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="cash_back",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "Cashbacks"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CashBack\CashBackTrekRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CashBack
{
    use UuidIdColumn;

    /**
     * @var string
     *
     *      *
     *
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
    protected $title;

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
    protected $description;

    /**
     * @ORM\Column(
     *     name="cashback_condition",
     *     type="text",
     *     length=65535,
     *     nullable=false,
     *     options={
     *         "fixed": false,
     *         "comment": "Условия кешбека",
     *     }
     * )
     *
     * @var string
     */
    protected $condition;

    /**
     * @ORM\Column(
     *     name="url",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "fixed": false,
     *         "comment": "адрес кешбек сервиса"
     *     }
     * )
     *
     * @var string
     */
    protected $url;

    /**
     * @ORM\Column(
     *     name="site_url",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "fixed": false,
     *         "comment": "адрес сайта-заказчика"
     *     }
     * )
     *
     * @var string
     */
    protected $siteUrl = '';

    /**
     * @ORM\Column(
     *     name="cash",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "fixed": false
     *     }
     * )
     *
     * @var string
     */
    protected $cash;

    /**
     * @ORM\Column(
     *     name="external_id",
     *     type="integer",
     *     nullable=true
     * )
     *
     * @var int
     */
    protected $externalId;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="AppBundle\Entity\Stock\CashBackPlatform",
     *     cascade={},
     *     fetch="LAZY",
     *     inversedBy="cashBacks"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="cash_back_platform_id",
     *     referencedColumnName="id"
     * )
     *
     * @var CashBackPlatform
     */
    protected $cashBackPlatform;

    /**
     * @ORM\OneToOne(
     *     targetEntity="AppBundle\Entity\Stock\CashBackImage",
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(
     *         name="cash_back_image_id",
     *         referencedColumnName="id",
     *         nullable=false,
     *     )
     * })
     *
     * @var CashBackImage
     */
    protected $cashBackImage;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\Stock\CashBackCategory",
     *     mappedBy="cashBack",
     *     orphanRemoval=true,
     *     cascade={"ALL"},
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @var ArrayCollection
     */
    protected $categories;

    /**
     * @ORM\Column(
     *     name="active",
     *     type="boolean"
     * )
     *
     * @var bool
     */
    protected $active = false;

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
    protected $status = CashBackStatusEnumType::STATUS_NOT_PARTNER;

    /**
     * @Doctrine\ORM\Mapping\Column(
     *     name="rating",
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
    protected $rating = 0;

    /**
     * @var string
     */
    protected $trekUrl;

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
