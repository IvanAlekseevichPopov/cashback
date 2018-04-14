<?php

declare(strict_types=1);

namespace App\Repository\CashBack;

use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * CashBackTrekRepository.
 */
class CashBackTrekRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @return int
     */
    public function getAwaitingCount(User $user): int
    {
        $qb = $this->createQueryBuilder('cbt');

        return (int) $qb
            ->select($qb->expr()->count('cbt'))
            ->join('cbt.balanceHistory', 'bh')
            ->where($qb->expr()->eq('cbt.user', ':user'))
            ->andWhere($qb->expr()->in('bh.status', ':status'))
            ->setParameter('user', $user->getId())
            ->setParameter('status', TransactionStatusEnumType::STATUS_WAIT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param User $user
     *
     * @return int
     */
    public function getConfirmedCount(User $user): int
    {
        $qb = $this->createQueryBuilder('cbt');

        return (int) $qb
            ->select($qb->expr()->count('cbt'))
            ->join('cbt.balanceHistory', 'bh')
            ->where($qb->expr()->eq('cbt.user', ':user'))
            ->andWhere($qb->expr()->in('bh.status', ':status'))
            ->setParameter('user', $user->getId())
            ->setParameter('status', TransactionStatusEnumType::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
