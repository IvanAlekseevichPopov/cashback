<?php

declare(strict_types=1);

namespace App\Entity;

use AppBundle\Traits\Doctrine\Accessor\UserAccessor;
use AppBundle\Traits\Doctrine\Column\AmountColumn;
use AppBundle\Traits\Doctrine\Column\CreatedAtColumn;
use AppBundle\Traits\Doctrine\Column\CurrencyColumn;
use AppBundle\Traits\Doctrine\Column\UuidIdColumn;
use AppBundle\Traits\Doctrine\LifecycleCallbacks\CreatedAtLifecycleTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JmsAnnotation;

/**
 * @ORM\Table(
 *     name="transaction",
 *     options={
 *         "collate": "utf8mb4_unicode_ci",
 *         "charset": "utf8mb4",
 *         "comment": "History of balance changes"
 *     }
 * )
 *
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 * @ORM\EntityListeners({"AppBundle\EventListener\BalanceHistorySubscriber"})
 * @ORM\HasLifecycleCallbacks
 */
class Transaction
{
    use UuidIdColumn;
    use AmountColumn;
    use CurrencyColumn;
    use CreatedAtColumn;

    use UserAccessor;

    use CreatedAtLifecycleTrait;

    /**
     * Пользователь.
     *
     * @ORM\ManyToOne(
     *     targetEntity="AppBundle\Entity\Users\User",
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
    protected $user;

    /**
     * Баланс
     *
     * @ORM\ManyToOne(
     *     targetEntity="AppBundle\Entity\Users\UserBalance",
     *     fetch="EXTRA_LAZY",
     *     inversedBy="balanceHistory"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(
     *     name="balance_id",
     *     referencedColumnName="id",
     * )
     *
     * @var UserBalance
     */
    protected $balance;

    /**
     * ID операции.
     *
     * @JMSAnnotation\Type("string")
     * @JMSAnnotation\Since("1.0")
     *
     * @ORM\Column(
     *     name="operation_id",
     *     type="UserBalanceOperationsEnumType",
     *     options={
     *         "comment": "ID операции"
     *     }
     * )
     *
     * @var string
     */
    protected $operationId;

    /**
     * Статус операции.
     *
     * @JMSAnnotation\Type("string")
     * @JMSAnnotation\Since("1.0")
     *
     * @ORM\Column(
     *     name="status",
     *     type="UserBalanceHistoryStatusEnumType",
     *     options={
     *         "comment": "Статус операции"
     *     }
     * )
     *
     * @var string
     */
    protected $status;

    /**
     * Комментарий.
     *
     * @JMSAnnotation\Type("string")
     * @JMSAnnotation\Since("1.0")
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
    protected $comment;

    /**
     * Текущий баланс после проведения транзакции.
     *
     * @JmsAnnotation\Type("double")
     * @JmsAnnotation\Since("1.0")
     *
     * @Doctrine\ORM\Mapping\Column(
     *     name="current_balance",
     *     type="decimal",
     *     precision=20,
     *     scale=8,
     *     nullable=false,
     *     options={
     *         "comment": "Текущий баланс"
     *     }
     * )
     *
     * @var float
     */
    protected $currentBalance = 0;

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
     * Сеттер ID операции.
     *
     * @param string $operationId
     *
     * @return $this
     */
    public function setOperationId(string $operationId)
    {
        $this->operationId = $operationId;

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
     * Геттер ID операции.
     *
     * @return string
     */
    public function getOperationId(): ?string
    {
        return $this->operationId;
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
}
