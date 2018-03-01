<?php

declare(strict_types = 1);

namespace App\Repository;

use AppBundle\DBAL\Types\Enum\Users\UserBalanceHistoryStatusEnumType;
use AppBundle\DBAL\Types\Enum\Users\UserBalanceOperationsEnumType;
use AppBundle\Entity\Users\Transaction;
use AppBundle\Entity\Users\UserBalanceHistory;
use AppBundle\Repository\EntityRepositoryAbstract;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

/**
 * TransactionRepository
 */
class TransactionRepository extends EntityRepository
{

    /**
     * Возвращает количество транзакций выводе после переданной
     *
     * @param UserBalanceHistory $transaction
     *
     * @param string             $status
     *
     * @return int
     */
    public function getCountOfWithdrawAfter(Transaction $transaction, $status = UserBalanceHistoryStatusEnumType::STATUS_APPROVED): int
    {
        $user = $transaction->getUser();

        $qb = $this->createQueryBuilder('ubh');

        return (int) $qb
            ->select('count(ubh.id)')
            ->where($qb->expr()->eq('ubh.user', ':user'))
            ->andWhere($qb->expr()->eq('ubh.amount', ':amount'))
            ->andWhere($qb->expr()->eq('ubh.operationId', ':operationId'))
            ->andWhere($qb->expr()->eq('ubh.status', ':status'))
            ->andWhere($qb->expr()->gte('ubh.createdAt', ':date'))
            ->setParameter('user', $user->getId())
            ->setParameter('amount', -$transaction->getAmount())
            ->setParameter('operationId', UserBalanceOperationsEnumType::BALANCE_OPERATION_WITHDRAW_PHONE)
            ->setParameter('status', $status)
            ->setParameter('date', $transaction->getCreatedAt(), Type::DATETIME)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Возвращает колчество выведеных денег и количество запросов
     * [
     *  'cnt'  => 100,
     *  'amnt' => -3423
     * ]
     *
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return array|null
     */
    public function getWithdrawStats(\DateTime $from, \DateTime $to): array
    {
        $qb = $this->createQueryBuilder('ubh');

        return $qb
            ->select('count(ubh.id) AS cnt, sum(ubh.amount) AS amnt')
            ->where($qb->expr()->eq('ubh.operationId', ':operation'))
            ->andWhere($qb->expr()->eq('ubh.status', ':status'))
            ->andWhere($qb->expr()->between('ubh.createdAt', ':from', ':to'))
            ->setParameter('from', $from, Type::DATETIME)
            ->setParameter('to', $to, Type::DATETIME)
            ->setParameter('operation', UserBalanceOperationsEnumType::BALANCE_OPERATION_WITHDRAW_PHONE)
            ->setParameter('status', UserBalanceHistoryStatusEnumType::STATUS_APPROVED)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает количество денег, ожидающих выведения
     * [
     *  'cnt'  => 15,
     *  'amnt' => 350
     * ]
     *
     * @return array|null
     */
    public function getAwaitingWithdrawStats(): ?array
    {
        $qb = $this->createQueryBuilder('ubh');

        return $qb
            ->select('count(ubh.id) AS cnt, sum(ubh.amount) AS amnt')
            ->where($qb->expr()->eq('ubh.operationId', ':operation'))
            ->andWhere($qb->expr()->eq('ubh.status', ':status'))
            ->setParameter('operation', UserBalanceOperationsEnumType::BALANCE_OPERATION_WITHDRAW_PHONE)
            ->setParameter('status', UserBalanceHistoryStatusEnumType::STATUS_WAIT)
            ->getQuery()
            ->getOneOrNullResult();
    }


}
