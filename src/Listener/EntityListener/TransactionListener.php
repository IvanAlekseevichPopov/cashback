<?php

declare(strict_types=1);

namespace App\Listener\EntityListener;

use App\Entity\Transaction;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TransactionListener.
 */
class TransactionListener
{
    /** @var ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postUpdate(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->recalc($transaction, $args);
    }

    public function postPersist(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->recalc($transaction, $args);
    }

    protected function recalc(Transaction $transaction, LifecycleEventArgs $args)
    {
        if ($transaction->getBalance()) {
            $this->container->get('app.manager.users.user_balance_manager')
                ->recalculateBalance($transaction->getBalance());
        }
    }
}
