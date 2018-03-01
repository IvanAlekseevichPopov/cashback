<?php

declare(strict_types=1);

namespace App\Repository\CashBack;

//use AppBundle\DBAL\Types\Enum\Users\UserBalanceHistoryStatusEnumType;
//use AppBundle\Entity\Users\User;
use Doctrine\ORM\EntityRepository;

/**
 * CashBackTrekRepository.
 */
class CashBackTrekRepository extends EntityRepository
{
    /**
     * Получить количество кешбеков в ожидании по пользователю.
     *
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
            ->setParameter('status', UserBalanceHistoryStatusEnumType::STATUS_WAIT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Количество подтвержденных кешбеков.
     *
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
            ->setParameter('status', UserBalanceHistoryStatusEnumType::STATUS_APPROVED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
