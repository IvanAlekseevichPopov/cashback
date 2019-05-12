<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\TransactionEnumType;
use App\DBAL\Types\Enum\TransactionStatusEnumType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listener\EntityListener\TransactionListener"})
 * @ORM\HasLifecycleCallbacks
 */
final class Transaction
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

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
    private $amount = 0.0;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="transactions"
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
     *     targetEntity="Balance",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="transactions"
     * )
     * @ORM\JoinColumn(
     *     name="balance_id",
     *     referencedColumnName="id",
     * )
     *
     * @var Balance
     */
    private $balance;

    /**
     * @ORM\Column(
     *     name="operation_type",
     *     type="TransactionEnumType",
     * )
     *
     * @var string
     */
    private $type = TransactionEnumType::CREATE;

    //TODO проверять при сохранении, что операция создания только одна

    /**
     * @ORM\Column(type="TransactionStatusEnumType")
     *
     * @var string
     */
    private $status = TransactionStatusEnumType::STATUS_WAIT;

    /**
     * @ORM\Column(
     *     name="comment",
     *     type="string",
     *     nullable=true,
     * )
     *
     * @var string
     */
    private $comment;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    public function setBalance(Balance $Balance): void
    {
        $this->balance = $Balance;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}
