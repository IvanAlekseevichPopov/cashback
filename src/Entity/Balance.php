<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="balance",
 *     options={
 *          "comment"="User balance"
 *     }
 * )
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Balance
{
    use IntegerAutoIncrementIdColumn;

    //TODO CurrencyColumn если понадобится

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
     * @var ArrayCollection|Transaction[]
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
        $this->transactions = new ArrayCollection;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function addTransaction(Transaction $transaction)
    {
        if (false === $this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setBalance($this);
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param Transaction $transaction
     */
    public function removeTransaction(Transaction $transaction)
    {
        // сам метод нужно оставить иначе не отрисовывается форма
        //$this->balanceHistory->removeElement($balanceHistory);
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Balance
     */
    public function setAmount(float $amount): Balance
    {
        $this->amount = $amount;

        return $this;
    }
}
