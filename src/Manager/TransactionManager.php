<?php

declare(strict_types=1);

namespace App\Manager;

use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class TransactionManager
{
    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->em = $manager;
    }

    /**
     * @param User $user
     */
    public function recalculateBalance(User $user)
    {
        $connection = $this->em->getConnection();

        $historySum = (float) $connection->executeQuery(
            'SELECT SUM(amount) FROM users_balances_history
                WHERE user_id=:user AND status=:status',
            [
                'user' => $user->getId(),
                'status' => TransactionStatusEnumType::STATUS_APPROVED,
            ]
        )->fetchColumn();

        //todo переписать на queryBuilder

        $balance = $user->getBalance();
        $balance->setAmount($historySum);

        $this->persist($balance);
        $this->flush();
    }

    /**
     * @param User   $user
     * @param float  $amount
     * @param string $type
     * @param string $status
     *
     * @return Transaction
     */
    public function changeBalance(User $user, float $amount, string $type, string $status): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setUser($user);
        $transaction->setType($type);
        $transaction->setStatus($status);

        $this->persistAndSave($transaction);

        return $transaction;
    }

    public function persist($entity): void
    {
        $this->em->persist($entity);
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    public function persistAndSave($entity): void
    {
        $this->persist($entity);
        $this->flush();
    }
}
