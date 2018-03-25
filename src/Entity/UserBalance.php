<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="users_balances",
 *     options={
 *          "comment"="Users balances"
 *     },
 *      uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="user_balance_relations",
 *                 columns={
 *                     "user_id",
 *                     "currency_id"
 *                 }
 *          )
 *     }
 * )
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class UserBalance
{
    use IntegerAutoIncrementIdColumn;

    //    use CurrencyColumn;
//    use AmountColumn;
//    use CreatedAtColumn;
//    use UpdatedAtColumn;
//
//    use UserAccessor;
//
//    use CreatedAtLifecycleTrait;
//    use UpdatedAtLifecycleTrait;

    /**
     * Пользователь
     *
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="balances"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     *
     * @var User
     */
    protected $user;

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
     * @var ArrayCollection
     */
    protected $transactions;

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
     * @param Transaction $transaction
     *
     * @return UserBalance
     */
    public function addBalanceHistory(Transaction $transaction): UserBalance
    {
        if (false === $this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
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
}
