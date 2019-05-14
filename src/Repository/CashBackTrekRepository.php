<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\CashBackTrek;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CashBackTrekRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CashBackTrek::class);
    }

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
