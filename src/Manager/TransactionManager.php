<?php

declare(strict_types = 1);

namespace App\Manager;

use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;

/**
 * TransactionManager.
 */
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
     * @param User  $user
     * @param float $amount
     * @param       $type
     * @param       $status
     *
     * @return Transaction
     */
    public function changeBalance(User $user, float $amount, $type, $status): Transaction
    {
        $transaction = (new Transaction())
            ->setAmount($amount)
            ->setUser($user)
            ->setType($type)
            ->setStatus($status);

        $this->persist($transaction);
        $this->flush();

        return $transaction;
    }

    public function persist($entity)
    {
        $this->em->persist($entity);
    }

    public function flush()
    {
        $this->em->flush();
    }

    public function persistAndSave($entity)
    {
        $this->persist($entity);
        $this->flush();
    }

}
