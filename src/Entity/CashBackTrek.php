<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\Column\UuidColumn;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashBackTrek.
 *
 * @ORM\Table(name="cash_back_trek")
 *
 * @ORM\Entity(repositoryClass="App\Repository\CashBack\CashBackTrekRepository")
 */
class CashBackTrek
{
    use UuidColumn;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinColumn(
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
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="date_immutable")
     */
    private $createdAt;

    public function __construct(User $user, CashBack $cashBack)
    {
        $this->user = $user;
        $this->cashBack = $cashBack;
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return CashBack
     */
    public function getCashBack(): CashBack
    {
        return $this->cashBack;
    }

    /**
     * @return Transaction|null
     */
    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
