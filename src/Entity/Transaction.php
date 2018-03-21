<?php

declare(strict_types = 1);

namespace App\Entity;

use App\DBAL\Types\Enum\TransactionEnumType;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * Transaction.
 *
 * @ORM\Table(
 *     name="transaction",
 *     options={
 *         "comment": "History of balance changes"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Transaction //* @ORM\EntityListeners({"AppBundle\EventListener\BalanceHistorySubscriber"}) //TODO перенести лисенер по необходимости
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

//    use AmountColumn;
//    use CurrencyColumn;
//    use CreatedAtColumn;
//
//    use UserAccessor;
//
//    use CreatedAtLifecycleTrait;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="User",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="balanceHistory"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id"
     * )
     *
     * @var User
     */
    private $user;

//    /**
//     * @ORM\ManyToOne(
//     *     targetEntity="AppBundle\Entity\Users\UserBalance",
//     *     fetch="EXTRA_LAZY",
//     *     inversedBy="balanceHistory"
//     * )
//     * @Doctrine\ORM\Mapping\JoinColumn(
//     *     name="balance_id",
//     *     referencedColumnName="id",
//     * )
//     *
//     * @var UserBalance
//     */
//    private $balance;

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
     * Статус операции.
     *
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
     * Комментарий.
     *
     * @ORM\Column(
     *     name="comment",
     *     type="string",
     *     nullable=true,
     *     options={
     *         "comment": "Комментарий"
     *     }
     * )
     *
     * @var string
     */
    private $comment;

    //TODO или привязаннй баланс или эта хрень, что-нить одно
//    /**
//     * @Doctrine\ORM\Mapping\Column(
//     *     name="current_balance",
//     *     type="decimal",
//     *     precision=20,
//     *     scale=8,
//     *     nullable=false,
//     *     options={
//     *         "comment": "Текущий баланс"
//     *     }
//     * )
//     *
//     * @var float
//     */
//    private $currentBalance = 0;

    public function __construct()
    {
        $this->type = TransactionEnumType::BALANCE_OPERATION_CREATE;
        //TODO проверять при сохранении, что операция создания только одна
    }

    /**
     * Сеттер баланса.
     *
     * @param UserBalance $userBalance
     *
     * @return $this
     */
    public function setBalance(UserBalance $userBalance)
    {
        $this->balance = $userBalance;

        return $this;
    }

    /**
     * Геттер баланса.
     *
     * @return UserBalance
     */
    public function getBalance(): ?UserBalance
    {
        return $this->balance;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Transaction
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set currentBalance.
     *
     * @param string $currentBalance
     *
     * @return Transaction
     */
    public function setCurrentBalance($currentBalance)
    {
        $this->currentBalance = $currentBalance;

        return $this;
    }

    /**
     * Get currentBalance.
     *
     * @return string
     */
    public function getCurrentBalance()
    {
        return $this->currentBalance;
    }

    /**
     * Сеттер статуса.
     *
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
     * Геттер статуса.
     *
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
}
