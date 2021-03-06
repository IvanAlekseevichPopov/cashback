<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackPlatform;
use App\Entity\User;
use App\Model\Query\CashbackQuery;
use App\Traits\UuidFinderTrait;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Type;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CashBackRepository extends ServiceEntityRepository
{
    use UuidFinderTrait;

    private const TOP_QUANTITY = 10;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CashBack::class);
    }

    public function getBySlug(string $slug, User $user = null)
    {
        $qb = $this->createQueryBuilder('cb');

        $qb
            ->select('cb, image')
            ->join('cb.cashBackImage', 'image'); //TODO join comments

        if (null === $user || !$user->isModerator()) {
            $qb
                ->andWhere($qb->expr()->eq('cb.active', ':active'))
                ->andWhere($qb->expr()->isNotNull('cb.description'))
                ->setParameter('active', true);
        }

        return $qb
            ->andWhere($qb->expr()->eq('cb.slug', ':slug'))
            ->andWhere($qb->expr()->eq('cb.status', ':status'))
            ->setParameter('slug', $slug)
            ->setParameter('status', CashBackStatusEnumType::APPROVED_PARTNERSHIP)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPlatformIds(CashBackPlatform $cashBackPlatform): array
    {
        $qb = $this->createQueryBuilder('cb');

        return $qb
            ->select('cb.externalId')
            ->where($qb->expr()->eq('cb.cashBackPlatform', ':platform'))
            ->andWhere($qb->expr()->isNotNull('cb.externalId'))
            ->setParameter('platform', $cashBackPlatform->getId())
            ->getQuery()
            ->getArrayResult();
    }

    public function getActiveCashBacks(CashbackQuery $query): array
    {
        $qb = $this->createQueryBuilder('cb');

        $qb
            ->andWhere($qb->expr()->in('cb.active', ':active'))
            ->andWhere($qb->expr()->in('cb.status', ':stat'))
            ->setParameter('stat', CashBackStatusEnumType::APPROVED_PARTNERSHIP)
            ->setParameter('active', true);

        if (null !== $query->title) {
            $qb
                ->andWhere($qb->expr()->like($qb->expr()->lower('cb.title'), ':title'))
                ->setParameter('title', '%'.mb_strtolower($query->title).'%');
        }

        return $qb
            ->setFirstResult($query->getFirstResult())
            ->setMaxResults($query->getPerPage())
            ->getQuery()
            ->getResult();
    }

    public function getCashBackCollectionForUpdate(CashBackPlatform $admitadPlatform, DateTime $date): ?array
    {
        $qb = $this->createQueryBuilder('cb');

        return $qb
            ->where($qb->expr()->eq('cb.cashBackPlatform', ':cashback_platform'))
            ->andWhere($qb->expr()->lt('cb.createdAt', ':date'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->in('cb.active', ':active'),
                        $qb->expr()->in('cb.status', ':approved')
                    ),
                    $qb->expr()->in('cb.status', ':awaiting')
                )
            )
            ->andWhere()
            ->setParameter('approved', CashBackStatusEnumType::APPROVED_PARTNERSHIP)
            ->setParameter('awaiting', CashBackStatusEnumType::AWAITING_PARTNERSHIP)
            ->setParameter('active', true)
            ->setParameter('date', $date, Type::DATETIME)
            ->setParameter('cashback_platform', $admitadPlatform)
            ->getQuery()
            ->getResult();
    }

    public function getCashbackTop(): array
    {
        $qb = $this->createQueryBuilder('cb');

        $qb
            ->andWhere($qb->expr()->in('cb.active', ':active'))
            ->andWhere($qb->expr()->in('cb.status', ':stat'))
            ->setParameter('stat', CashBackStatusEnumType::APPROVED_PARTNERSHIP)
            ->setParameter('active', true);

        return $qb
            ->setMaxResults(self::TOP_QUANTITY)
            ->getQuery()
            ->getResult();
    }
}
