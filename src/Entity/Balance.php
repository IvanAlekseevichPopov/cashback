<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Balance
{
    use IntegerAutoIncrementIdColumn;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Transaction",
     *     mappedBy="balance",
     *     cascade={
     *         "persist",
     *         "remove"
     *     }
     * )
     *
     * @var Collection|Transaction[]
     */
    protected $transactions;

    /**
     * @ORM\Column(
     *     type="decimal",
     *     precision=16,
     *     scale=4,
     *     nullable=false,
     * )
     *
     * @var float
     */
    protected $amount = 0;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function addTransaction(Transaction $transaction): void
    {
        if (false === $this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setBalance($this);
        }
    }

    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function removeTransaction(Transaction $transaction): void
    {
        // сам метод нужно оставить иначе не отрисовывается форма
        //$this->balanceHistory->removeElement($balanceHistory);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}
