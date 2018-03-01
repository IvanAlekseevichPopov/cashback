<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use AppBundle\Entity\Stock\CashBack;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="cash_back_category",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "Cashback categories"
 *     }
 * )
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CashBackCategory
{
    use IntegerAutoIncrementIdColumn;

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
     *
     * @var string
     */
    protected $title;

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
     *     targetEntity="App\Entity\CashBack",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumns({
     *     @ORM\JoinColumn(
     *         name="cash_back_id",
     *         referencedColumnName="id"
     *     )
     * })
     *
     * @var CashBack
     */
    private $cashBack;

    /**
     * @return string|null
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
     * @return string|null
     */
    public function getCash(): ?string
    {
        return $this->cash;
    }

    /**
     * @param string $cash
     *
     * @return $this
     */
    public function setCash(string $cash)
    {
        $this->cash = $cash;

        return $this;
    }

    /**
     * @return CashBack
     */
    public function getCashBack(): CashBack
    {
        return $this->cashBack;
    }

    /**
     * @param CashBack $cashBack
     *
     * @return $this
     */
    public function setCashBack(CashBack $cashBack)
    {
        $this->cashBack = $cashBack;

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
}
