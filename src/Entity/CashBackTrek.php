<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Traits\Column\IntegerAutoIncrementIdColumn;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashBackTrek.
 *
 * @ORM\Table(
 *     name="cash_back_trek",
 *     options={
 *         "comment": "Cashback treks"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\CashBack\CashBackTrekRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CashBackTrek
{
    use IntegerAutoIncrementIdColumn;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     fetch="EXTRA_LAZY"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="CashBack",
     *     fetch="EXTRA_LAZY"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="cash_back_id",
     *     referencedColumnName="id"
     * )
     *
     * @var CashBack
     */
    private $cashBack;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Transaction",
     *     fetch="LAZY",
     * )
     * @ORM\JoinColumn(
     *     name="transaction_id",
     *     nullable=true,
     *     referencedColumnName="id"
     * )
     *
     * @var Transaction|null
     */
    private $transaction;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

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
     * @return Transaction|null
     */
    public function getBalanceHistory(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setBalanceHistory(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }
}
