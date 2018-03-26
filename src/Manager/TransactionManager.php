<?php

declare(strict_types = 1);

namespace App\Manager;

use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\Balance;
use App\Entity\Transaction;
use App\Entity\User;
use Deployer\Component\PharUpdate\Manager;
use Deployer\Component\PharUpdate\Manifest;
use Doctrine\ORM\EntityManagerInterface;


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

        $historySum = (float)$connection->executeQuery(
            'SELECT SUM(amount) FROM users_balances_history
                WHERE user_id=:user AND status=:status', [
            'user'     => $user->getId(),
            'status'   => TransactionStatusEnumType::STATUS_APPROVED,
        ])->fetchColumn();

        $balance = $user->getBalance();
        $balance->setAmount($historySum);

        $this->em->persist($balance);
        $this->em->flush();
    }
}
