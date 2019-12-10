<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CashBackCategory
{
    use IntegerAutoIncrementIdColumn; //TODO uuid + slug

    /**
     * @ORM\Column(
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
    private $title;

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
     *     targetEntity="App\Entity\CashBack",
     *     inversedBy="categories"
     * )
     * @ORM\JoinColumn(
     *     name="cash_back_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     *
     * @var CashBack
     */
    private $cashBack;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getCash(): ?string
    {
        return $this->cash;
    }

    public function setCash(string $cash): void
    {
        $this->cash = $cash;
    }

    public function getCashBack(): CashBack
    {
        return $this->cashBack;
    }

    public function setCashBack(CashBack $cashBack): void
    {
        $this->cashBack = $cashBack;
    }

    public function getExternalId(): ?int
    {
        return $this->externalId;
    }

    public function setExternalId(int $externalId): void
    {
        $this->externalId = $externalId;
    }
}
