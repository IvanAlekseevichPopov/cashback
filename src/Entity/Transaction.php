<?php

declare(strict_types=1);

namespace App\Entity;

use App\DBAL\Types\Enum\TransactionEnumType;
use App\Traits\Column\UuidColumn;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Transaction.
 *
 * @ORM\Table(name="transaction")
 *
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\EntityListeners({"App\Listener\EntityListener\TransactionListener"})
 * @ORM\HasLifecycleCallbacks
 */
class Transaction
{
    use UuidColumn;

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
     *     options={
     *         "comment": "operation type"
     *     }
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\Column(
     *     name="status",
     *     type="TransactionStatusEnumType",
     *     options={
     *         "comment": "Статус операции"
     *     }
     * )
     *
     * @var string
     */
    private $status;

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
        $this->type = TransactionEnumType::BALANCE_OPERATION_CREATE;
        //TODO проверять при сохранении, что операция создания только одна
    }

    /**
     * @param Balance $Balance
     *
     * @return $this
     */
    public function setBalance(Balance $Balance)
    {
        $this->balance = $Balance;

        return $this;
    }

    /**
     * @return Balance
     */
    public function getBalance(): ?Balance
    {
        return $this->balance;
    }

    /**
     * @param string $comment
     *
     * @return Transaction
     */
    public function setComment(string $comment): Transaction
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Uuid
     */
    public function getId(): Uuid
    {
        return $this->id;
    }

    /**
     * @param Uuid $id
     *
     * @return $this
     */
    public function setId(Uuid $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return Transaction
     */
    public function setUser(User $user): Transaction
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Transaction
     */
    public function setType(string $type): Transaction
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Transaction
     */
    public function setAmount(float $amount): Transaction
    {
        $this->amount = $amount;

        return $this;
    }
}
