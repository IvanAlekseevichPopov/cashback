<?php

declare(strict_types=1);

namespace AppBundle\Manager\Users;

use AppBundle\DBAL\Types\Enum\Users\UserBalanceHistoryStatusEnumType;
use AppBundle\DBAL\Types\Enum\Users\UserBalanceOperationsEnumType;
use AppBundle\DBAL\Types\Enum\YandexMoneyTokenTypeEnumType;
use AppBundle\Entity\Currencies\Currency;
use AppBundle\Entity\MobileAppConfig;
use AppBundle\Entity\Users\User;
use AppBundle\Entity\Users\UserBalance;
use AppBundle\Entity\Users\UserBalanceHistory;
use AppBundle\Exceptions\AppException;
use AppBundle\Exceptions\CurrencyNotFoundException;
use AppBundle\Manager\AppManagerAbstract;
use AppBundle\Manager\Currencies\CurrencyManager;
use AppBundle\Model\Api\UserBalanceWithdraw;
use AppBundle\QueueKey;
use AppBundle\Traits\AwareTraits\Manager\Currency\CurrencyManagerAwareTrait;
use AppBundle\Traits\AwareTraits\Services\QueueProducerAwareTrait;
use AppBundle\Utils\UserBalanceTransaction;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TransactionManager
 */
class TransactionManager extends AppManagerAbstract
{
//    use CurrencyManagerAwareTrait,
//        QueueProducerAwareTrait;
//
//    const FIRST_TRANSACTION_COMMENT = 'Открывающая транзакция';
//    /** Тип транзакции для вывода */
//    protected $orderWithdrawType = YandexMoneyTokenTypeEnumType::TYPE_OTHER;
//    /** Флаг, необходимо ли делать пуш */
//    protected $needPush = false;
//
//    /**
//     * @param CurrencyManager $currencyManager
//     * @param EntityManager $entityManager
//     * @param \Redis $redisStorage
//     * @param ContainerInterface $container
//     */
//    public function __construct(
//        CurrencyManager $currencyManager, EntityManager $entityManager, \Redis $redisStorage,
//        ContainerInterface $container
//    )
//    {
//        parent::__construct($entityManager, $redisStorage, $container);
//
//        $this->setCurrencyManager($currencyManager);
//    }
//
//    /**
//     * Добавление недостающих балансов клиентов
//     *
//     * @param User $user
//     *
//     * @return void
//     */
//    public function createUserBalances(User $user)
//    {
//        foreach ($this->getCurrencyManager()->loadAll() as $currency) {
//            $userBalance = $this->findOneByUserAndBalanceCurrency($user, $currency);
//
//            if (null === $userBalance) {
//                $this->addBalance($user, $currency);
//            }
//        }
//    }
//
//    /**
//     * Создание баланса пользователя
//     *
//     * @param User $user
//     * @param Currency $currency
//     *
//     * @return UserBalance
//     * @throws \ErrorException
//     */
//    public function addBalance(User $user, Currency $currency): UserBalance
//    {
//        $userBalance = (new UserBalance)
//            ->setUser($user)
//            ->setCurrency($currency)
//            ->setAmount(0.0);
//
//        $user->addBalance($userBalance);
//        $this->persistAndSave($userBalance);
//
//        $user->addBalance($userBalance);
//
//        return $userBalance;
//    }
//
//    public function getTransactionOptions(): UserBalanceTransaction
//    {
//        return new UserBalanceTransaction();
//    }
//
//    /**
//     * Простой метод для изменения баланса
//     *
//     * @param User $user
//     * @param float $amount
//     * @param string $comment
//     *
//     * @return UserBalanceHistory
//     */
//    public function simpleChangeRuBalance(User $user, float $amount, string $comment): UserBalanceHistory
//    {
//        $userBalance = $this->getBalanceByCurrencyId($user, Currency::TYPE_RUB);
//
//        $options = $this->getTransactionOptions()
//                        ->setComment($comment)
//                        ->setOperationId(UserBalanceOperationsEnumType::BALANCE_OPERATION_DECREASE)
//                        ->setStatus(UserBalanceHistoryStatusEnumType::STATUS_APPROVED);
//
//        return $this->changeBalance($userBalance, $amount, $options);
//    }
//
//    /**
//     * Изменение баланса
//     *
//     * @param UserBalance $balance
//     * @param float $amount Для списания передать отрицательное значение, для начисления положительное
//     * @param UserBalanceTransaction $options
//     *
//     * @return UserBalanceHistory
//     * @throws \ErrorException
//     * @throws \Exception
//     */
//    public function changeBalance(UserBalance $balance, float $amount, ?UserBalanceTransaction $options = null): UserBalanceHistory
//    {
//        $balance->setAmount($balance->getAmount() + $amount);
//
//        $transaction = (new UserBalanceHistory)
//            ->setBalance($balance)
//            ->setAmount($amount)
//            ->setCurrency($balance->getCurrency())
//            ->setCurrentBalance($balance->getAmount())
//            ->setUser($balance->getUser())
//            ->setStatus($options && $options->getStatus() ? $options->getStatus() : UserBalanceHistoryStatusEnumType::STATUS_APPROVED);
//
//        if ($options && $options->getOperationId()) {
//            $transaction->setOperationId($options->getOperationId());
//        } elseif ($amount < 0) {
//            $transaction->setOperationId(UserBalanceOperationsEnumType::BALANCE_OPERATION_DECREASE);
//        } elseif ($amount > 0) {
//            $transaction->setOperationId(UserBalanceOperationsEnumType::BALANCE_OPERATION_INCREASE);
//        }
//
//        if ($options) {
//            $transaction->setComment($options->getComment());
//        }
//
//        if(empty($options) || $options->isNeedPersist()) {
//            $this->persistAndSave($transaction);
//            $this->persistAndSave($balance);
//        }
//
//        return $transaction;
//    }
//
//    /**
//     * Возвращает нужный баланс для пользователя по id валюты
//     *
//     * @param User $user
//     * @param int $currencyId
//     * @param bool $needFlush
//     *
//     * @return UserBalance|null
//     * @throws \ErrorException
//     * @throws CurrencyNotFoundException
//     */
//    public function getBalanceByCurrencyId(User $user, int $currencyId, bool $needFlush = true): UserBalance
//    {
//        // ищем баланс с нужной валютой
//        $userBalance = $user->getBalances()->filter(function (UserBalance $entity) use ($currencyId) {
//            return (int)$entity->getCurrency()->getId() === $currencyId;
//        })->first();
//
//        if (empty($userBalance)) {
//            $currency = $this->getEntityManager()->getRepository(Currency::class)->find($currencyId);
//            if (null === $currency) {
//                throw new CurrencyNotFoundException('Currency not found(id - ' . $currencyId . ')');
//            }
//
//            $userBalance = $this->addBalance($user, $currency);
//
//            $this->persist($userBalance);
//            if ($needFlush) {
//                $this->flush();
//            }
//        }
//
//        return $userBalance;
//    }
//
//    /**
//     * Пересчитывание баланса по истории операций
//     *
//     * @param UserBalance $userBalance
//     *
//     * @throws \Doctrine\DBAL\DBALException
//     */
//    public function recalculateBalance(UserBalance $userBalance): void
//    {
//        $connection = $this->getEntityManager()->getConnection();
//
//        $historySum = (float)$connection->executeQuery(
//            'SELECT SUM(amount) FROM users_balances_history
//                WHERE user_id=:user AND currency_id=:currency AND status=:status', [
//            'user'     => $userBalance->getUser()->getId(),
//            'currency' => $userBalance->getCurrency()->getId(),
//            'status'   => UserBalanceHistoryStatusEnumType::STATUS_APPROVED,
//        ])->fetchColumn();
//
//        $userBalance->setAmount($historySum);
//
//        $this->persist($userBalance);
//        $this->flush();
//    }
//
//    /**
//     * Запрос вывода денег на телефон
//     *
//     * @param User   $user
//     * @param float  $amount
//     * @param string $comment
//     *
//     * @return UserBalanceHistory
//     * @throws AppException
//     */
//    public function orderWithDrawPhone(User $user, float $amount, string $comment): UserBalanceHistory
//    {
//        if(!$balance = $this->getBalanceByCurrencyId($user, Currency::TYPE_RUB))
//        {
//            $this->logger->critical('Не удалось выбрать баланс юзера');
//            throw new AppException('Произошла внутренняя ошибка #251');
//        }
//
//        if ($amount < 0) {
//            throw new AppException('Вывод на телефон не может быть отрицательным');
//        }
//
//        if (!$user->getUserPhone()) {
//            throw new AppException('Номер телефона не задан');
//        }
//
//        $phoneNumber = preg_replace('#\D#', '', $user->getUserPhone());
//        if (!preg_match('#^79\d{9}$#', $phoneNumber)) {
//            throw new AppException('Неверный формат телефона');
//        }
//
//        if(!$userBalance = $this->getBalanceByCurrencyId($user, Currency::TYPE_RUB)){
//            $this->logger->critical('Не удалось выбрать баланс юзера');
//            throw new AppException('Произошла внутренняя ошибка #269');
//        }
//
//        $this->recalculateBalance($userBalance);
//
//        if ($amount > $userBalance->getAmount()) {
//            throw new AppException('Максимальная сумма для вывода ' . $userBalance->getAmount() . ' рублей');
//        }
//
//        $transaction = new UserBalanceHistory();
//        $transaction
//            ->setUser($user)
//            ->setBalance($balance)
//            ->setComment($comment)
//            ->setAmount(-$amount)
//            ->setCurrency($balance->getCurrency())
//            ->setStatus(UserBalanceHistoryStatusEnumType::STATUS_WAIT)
//            ->setOperationId(UserBalanceOperationsEnumType::BALANCE_OPERATION_WITHDRAW_PHONE);
//
//        // проверки для модерации транзакций
//        $this->preModerationTransaction($transaction);
//
//        $this->persist($transaction);
//        $this->flush();
//
//        if (UserBalanceHistoryStatusEnumType::STATUS_WAIT === $transaction->getStatus()) {
//            $this->queueProducer->doHighBackground(
//                QueueKey::WITHDRAW_PHONE,
//                json_encode([
//                                'id'   => $transaction->getId(),
//                                'push' => $this->needPush,
//                                'type' => $this->orderWithdrawType,
//                            ])
//            );
//        }
//
//        return $transaction;
//    }
//    /**
//     * Удаляет все балансы юзера
//     *
//     * @param User $user
//     *
//     * @return void
//     */
//    public function removeBalancesByUser(User $user): void
//    {
//        $sql  = 'DELETE FROM users_balances WHERE user_id = :userId';
//        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
//
//        $stmt->execute([':userId' => $user->getId()]);
//    }
//
//    /**
//     * Геттер типа транзакции
//     *
//     * @return mixed
//     */
//    public function getOrderWithdrawType(): string
//    {
//        return $this->orderWithdrawType;
//    }
//
//    /**
//     * Сеттер типа транзакции
//     *
//     * @param mixed $orderWithdrawType
//     *
//     * @return $this
//     * @throws \Exception
//     */
//    public function setOrderWithdrawType(string $orderWithdrawType)
//    {
//        if(!isset(YandexMoneyTokenTypeEnumType::getChoices()[$orderWithdrawType])) {
//            throw  new \Exception('No such type. See YandexMoneyTokenTypeEnumType..');
//        }
//
//        $this->orderWithdrawType = $orderWithdrawType;
//
//        return $this;
//    }
//
//    /**
//     * Геттер флага необходимости пуша после транзакции
//     *
//     * @return mixed
//     */
//    public function getNeedPush(): bool
//    {
//        return $this->needPush;
//    }
//
//    /**
//     * Сеттер флага необходимости пуша после транзакции
//     *
//     * @param bool $needPush
//     *
//     * @return $this
//     */
//    public function setNeedPush(bool $needPush)
//    {
//        $this->needPush = $needPush;
//
//        return $this;
//    }
//
//    /**
//     * Получение баланса для пользователя и указанной валюты
//     *
//     * @param User $user
//     * @param Currency $balanceCurrency
//     *
//     * @return null|UserBalance
//     */
//    protected function findOneByUserAndBalanceCurrency(User $user, Currency $balanceCurrency)
//    {
//        /** @var UserBalance $userBalance */
//        $userBalance = $this
//            ->getRepository()
//            ->findOneBy(['user' => $user, 'currency' => $balanceCurrency]);
//
//        return $userBalance;
//    }
//
//    protected function preModerationTransaction(UserBalanceHistory $transaction)
//    {
//        $mobileConfig = $this->getEntityManager()
//                             ->getRepository(MobileAppConfig::class)
//                             ->find(1)
//                             ->getArrayConfig();
//
//        $maxAmount = $mobileConfig['max_payment'] ?? UserBalanceWithdraw::MINIMAL_GETTING_MONEY_AMOUNT;
//
//        if (abs($transaction->getAmount()) > $maxAmount) {
//            $transaction->setStatus(UserBalanceHistoryStatusEnumType::STATUS_WAIT_MODERATION);
//
//            return;
//        }
//
//        $dayLimit = $mobileConfig['payment_day_limit'] ?? 1;
//
//        /** @var UserBalanceHistory $lastWithdraw */
//        $lastWithdraw = $this->getEntityManager()
//            ->getRepository(UserBalanceHistory::class)
//            ->findOneBy([
//                           'balance'     => $transaction->getBalance(),
//                           'operationId' => UserBalanceOperationsEnumType::BALANCE_OPERATION_WITHDRAW_PHONE,
//                       ], ['createdAt' => 'DESC']);
//        if ($lastWithdraw && $lastWithdraw->getCreatedAt() > new \DateTime('-' . $dayLimit . ' day')) {
//            throw new AppException('Превышен лимит на количество запросов в сутки', 400);
//        }
//    }
//
//    /**
//     * @inheritdoc
//     *
//     * @return \AppBundle\Repository\Users\UserBalanceRepository|\Doctrine\ORM\EntityRepository
//     */
//    public function getRepository()
//    {
//        return $this->getEntityManager()->getRepository(UserBalance::class);
//    }
}
