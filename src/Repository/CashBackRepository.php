<?php

declare(strict_types=1);

namespace App\Repository;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBackPlatform;
use App\Entity\User;
use App\Traits\UuidFinderTrait;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class CashBackRepository extends EntityRepository
{
    use UuidFinderTrait;

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
            ->setParameter('status', CashBackStatusEnumType::STATUS_APPROVED_PARTNERSHIP)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param CashBackPlatform $cashBackPlatform
     *
     * @return array
     */
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

    /**
     * Возвращает коллекцию активных кешбеков.
     *
     * @param int        $offset
     * @param int        $limit
     * @param array|null $filters
     *
     * @return array
     */
    public function getActiveCashBacks(int $offset = 0, int $limit = 100, ?array $filters = null): array
    {
        $qb = $this->createQueryBuilder('cb');

        $qb
            ->andWhere($qb->expr()->in('cb.active', ':active'))
            ->andWhere($qb->expr()->in('cb.status', ':stat'))
            ->setParameter('stat', CashBackStatusEnumType::STATUS_APPROVED_PARTNERSHIP)
            ->setParameter('active', true)
            ->orderBy('cb.rating', 'DESC');

        if (null !== $filters) {
            foreach ($filters as $filter => $value) {
                if ('title' === $filter) {
                    $qb
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->like('cb.title', ':'.$filter),
                                $qb->expr()->like('cb.description', ':'.$filter)
                            )
                        )
                        ->setParameter($filter, '%'.$value.'%');
                }
            }
        }

        return $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Возвращает коллекцию для обновления с адмитада.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param \DateTime        $date
     *
     * @return array
     */
    public function getCashBackCollectionForUpdate(CashBackPlatform $admitadPlatform, \DateTime $date): ?array
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
            ->setParameter('approved', CashBackStatusEnumType::STATUS_APPROVED_PARTNERSHIP)
            ->setParameter('awaiting', CashBackStatusEnumType::STATUS_AWAITING_PARTNERSHIP)
            ->setParameter('active', true)
            ->setParameter('date', $date, Type::DATETIME)
            ->setParameter('cashback_platform', $admitadPlatform)
            ->getQuery()
            ->getResult();
    }
}
