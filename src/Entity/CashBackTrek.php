<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="cash_back_trek")
 * @ORM\Entity(repositoryClass="App\Repository\CashBackTrekRepository")
 */
class CashBackTrek
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

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
     * @var DateTimeImmutable
     *
     * @ORM\Column(type="date_immutable")
     */
    private $createdAt;

    public function __construct(User $user, CashBack $cashBack)
    {
        $this->id = Uuid::uuid4();
        $this->user = $user;
        $this->cashBack = $cashBack;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCashBack(): CashBack
    {
        return $this->cashBack;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
