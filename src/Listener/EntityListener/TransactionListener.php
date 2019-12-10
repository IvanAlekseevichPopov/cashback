<?php

declare(strict_types=1);

namespace App\Listener\EntityListener;

use App\Entity\Transaction;
use App\Manager\TransactionManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class TransactionListener
{
    /** @var TransactionManager */
    protected $transactionManager;

    public function __construct(TransactionManager $manager)
    {
        $this->transactionManager = $manager;
    }

    public function postUpdate(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->recalc($transaction, $args);
    }

    public function postPersist(Transaction $transaction, LifecycleEventArgs $args)
    {
        $this->recalc($transaction, $args);
    }

    protected function recalc(Transaction $transaction)
    {
        if ($transaction->getBalance()) {
            $this->transactionManager->recalculateBalance($transaction->getUser());
        }
    }
}
