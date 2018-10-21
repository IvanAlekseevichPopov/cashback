<?php

declare(strict_types = 1);

namespace App\Service;

use App\Entity\CashBack;
use App\Entity\CashBackTrek;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class CashbackRedirectHandler
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @param User                   $user
     * @param CashBack               $cashBack
     *
     * @return string
     */
    public function generateRedirectUrl(CashBack $cashBack, User $user = null): string
    {
        $cashBackTrek = new CashBackTrek($user, $cashBack);

        $this->entityManager->persist($cashBackTrek);
        $this->entityManager->flush();

        return sprintf('%s?subid=%s', $cashBack->getUrl(), $cashBackTrek->getId());
    }
}