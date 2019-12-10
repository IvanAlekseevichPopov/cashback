<?php

declare(strict_types=1);

namespace App\Command;

use App\DBAL\Types\Enum\TransactionEnumType;
use App\DBAL\Types\Enum\TransactionStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackPlatform;
use App\Entity\CashBackTrek;
use App\Entity\Transaction;
use App\Manager\TransactionManager;
use App\Repository\CashBackRepository;
use App\Service\AdmitadApiHandler;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetCashBackAdmitadCommand extends Command
{
    public const TMP_FILE = '/tmp/temporary_image_cashback';

    /** @var AdmitadApiHandler */
    protected $admitadApiHandler;

    /** @var TransactionManager */
    protected $transactionManager;

    /** @var EntityManagerInterface */
    protected $em;

    protected $admitadIds = null;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(AdmitadApiHandler $admitadApiHandler, EntityManagerInterface $manager, TransactionManager $transactionManager, LoggerInterface $logger)
    {
        $this->admitadApiHandler = $admitadApiHandler;
        $this->em = $manager;
        $this->transactionManager = $transactionManager;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:cashback:update')
            ->setDescription('Update cashback data from external sevice');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START');
        $admitadPlatform = $this->admitadApiHandler->getAdmitadPlatform();
        $this->admitadApiHandler->updateAccessToken($admitadPlatform);

        $now = new \DateTime();
        //Добавление новых площадок
        $this->addNewCashBacks($admitadPlatform);

        //Обновление существующих площадок
        $updatedCount = $this->updateExistCashBacks($admitadPlatform, $now);
        $output->writeln('Обновлено - '.$updatedCount);

        //Обновленые данных о выплатах, начисление кешбеков
        $this->updatePayments($admitadPlatform);

        $output->writeln('STOP');
    }

    /**
     * Берет акции адмитада с limit и offset.
     *
     * @param CashBackPlatform $admitadPlatform
     *
     * @return int
     */
    protected function addNewCashBacks(CashBackPlatform $admitadPlatform): int
    {
        $offset = 0;
        $limit = 10;
        $fullCounter = 0;

        do {
            $admitadResponse = $this->admitadApiHandler->getCampaigns($admitadPlatform, $offset, $limit);

            $count = 0;
            foreach ($admitadResponse['results'] as $item) {
                ++$count;

                if (false === $item['allow_deeplink']) {
                    $this->logAndShow($item['id'].'.Deeplink not supported');
                    continue;
                }

                if (in_array((int) $item['id'], $this->getAdmitadIds($admitadPlatform), true)) {
                    $this->logAndShow($item['id'].'.Already added to db');
                    continue;
                }

                $regionFlag = false;
                foreach ($item['regions'] as $region) {
                    if ('RU' === (string) $region['region']) {
                        $regionFlag = true;
                        break;
                    }
                }
                if (!$regionFlag) {
                    $this->logAndShow($item['id'].'.Region no RU');
                    continue;
                }

                $trafficFlag = false;
                foreach ($item['traffics'] as $traffic) {
                    if ('cashback' === strtolower($traffic['name'])) {
                        if (true === $traffic['enabled']) {
                            $trafficFlag = true;
                        }
                        break;
                    }
                }
                if (!$trafficFlag) {
                    $this->logAndShow($item['id'].'.Traffic flag bad');
                    continue;
                }

                $this->admitadApiHandler->createCashBack($admitadPlatform, $item, true);
            }

            $offset += $limit;
            $fullCounter += $count;
        } while ($count === $limit);

        return $fullCounter;
    }

    /**
     * Возвращает массив внешних id адмитада забинденых в системе.
     *
     * @param CashBackPlatform $admitadPlatform
     *
     * @return array
     */
    protected function getAdmitadIds(CashBackPlatform $admitadPlatform): array
    {
        if (null === $this->admitadIds) {
            $rawResult = $this->em->getRepository(CashBack::class)->findPlatformIds($admitadPlatform);
            $this->admitadIds = [];
            foreach ($rawResult as $rawItem) {
                $this->admitadIds[] = $rawItem['externalId'];
            }
        }

        return $this->admitadIds;
    }

    /**
     * Обновление уже существующих площадок.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param \DateTime        $now
     *
     * @return int
     */
    protected function updateExistCashBacks(CashBackPlatform $admitadPlatform, \DateTime $now): int
    {
        $cashBackCollection = $this->getCashBackRepository()->getCashBackCollectionForUpdate($admitadPlatform, $now);

        $updatesCounter = 0;
        /** @var CashBack $cashBack */
        foreach ($cashBackCollection as $cashBack) {
            $admitadResponse = $this->admitadApiHandler->checkCampaign($admitadPlatform, $cashBack);

            if (isset($admitadResponse['update_result']) && $admitadResponse['update_result']) {
                ++$updatesCounter;
            }
        }

        return $updatesCounter;
    }

    /**
     * Обновляет информацию о начислениях за кешбеки.
     *
     * @param $admitadPlatform
     */
    protected function updatePayments($admitadPlatform)
    {
        $offset = 0;
        $limit = 100;

        do {
            $paymentsCollection = $this->admitadApiHandler->getCashBackPayments($admitadPlatform, $offset, $limit);

            foreach ($paymentsCollection['results'] as $payment) {
                if (empty($payment['subid'])) {
                    $this->logger->warning('empty SubID');
                    continue;
                }

                //Переход без кешбека - нечего обрабатывать
                if (
                    0.0 === (float) $payment['payment_sum'] &&
                    0.0 === (float) $payment['payment_sum_approved'] &&
                    0.0 === (float) $payment['payment_sum_declined']
                ) {
                    continue;
                }

                /** @var CashBackTrek $cashBackTrek */
                $cashBackTrek = $this->getCashBackTrekRepository()->find($payment['subid']);
                if (null === $cashBackTrek) {
                    $this->logger->warning('not found cashBack trek -'.$payment['subid']);
                    continue;
                }

                $cashBackTransaction = $cashBackTrek->getTransaction();

                if (!empty($cashBackTransaction)) {
                    if (TransactionStatusEnumType::STATUS_APPROVED === $cashBackTransaction->getStatus() || TransactionStatusEnumType::STATUS_REJECT === $cashBackTransaction->getStatus()) {
                        //Транзакция закрыта - кешбек уже обработан не нужно ничего делать
                        continue;
                    }
                }

                if ($payment['payment_sum_open'] > 0) {
                    if (null === $cashBackTransaction && $payment['payment_sum_open'] === $payment['payment_sum']) {
                        //создаем неподтвержденную проводку, прикрепляем ее к треку
                        $this->createCashBackTransaction($cashBackTrek, (float) $payment['payment_sum_open'], TransactionStatusEnumType::STATUS_WAIT);

//                        $this->getPushSender()->sendPush($cashBackTrek->getUser(), PushMessage::MESSAGE_CASHBACK_ON_REVIEW);
                    }
                }

                //Если сумма "на рассмотрении" нулевая, т.е. кешбек или принят или отклонен
                //Только когда она нулевая, можно зачислять/отклонять нашу транзакцию на ожидании. Чтоб не заблудится в промежуточных состояниях
                if (0 === (int) $payment['payment_sum_open']) {
                    //Подтвержденая сумма не нулевая
                    if ($payment['payment_sum_approved'] > 0) {
                        if (empty($cashBackTransaction)) {
                            //Если транзакции еще не было создано - создаем с подтвержденной суммой и не паримся
                            $this->createCashBackTransaction($cashBackTrek, (float) $payment['payment_sum_approved'], TransactionStatusEnumType::STATUS_APPROVED);

//                            $this->getPushSender()->sendPush($cashBackTrek->getUser(), PushMessage::MESSAGE_CASHBACK_CONFIRMED);
                            continue;
                        }

                        if (TransactionStatusEnumType::STATUS_WAIT === $cashBackTransaction->getStatus()) {
                            //Если сумма в транзакции не совпадает с подтвержденной суммой - меняем сумму транзакции(в случае, когда кешбек подтвержден частично)
                            if ($cashBackTransaction->getAmount() !== (float) $payment['payment_sum_approved']) {
                                $cashBackTransaction->setAmount((float) $payment['payment_sum_approved']);
                                $this->logAndShow('Transaction sum has changed from '.$cashBackTransaction->getAmount().' to '.$payment['payment_sum_approved']);
                            }

                            $cashBackTransaction->setStatus(TransactionStatusEnumType::STATUS_APPROVED);
                            $this->em->persist($cashBackTransaction);
                            $this->em->flush();

//                            $this->getPushSender()->sendPush($cashBackTrek->getUser(), PushMessage::MESSAGE_CASHBACK_CONFIRMED);
                        }
                    }

                    //Если вся сумма транзакции отвергнута
                    if ($payment['payment_sum_declined'] > 0) {
                        if (empty($cashBackTransaction)) {
                            //Если транзакции еще не было создано - создаем транзакцию со статусом REJECTED
                            $this->createCashBackTransaction($cashBackTrek, (float) $payment['payment_sum_declined'], TransactionStatusEnumType::STATUS_REJECT);

//                            $this->getPushSender()->sendPush($cashBackTrek->getUser(), PushMessage::MESSAGE_CASHBACK_REJECTED);
                        } elseif ($cashBackTransaction->getAmount() === (float) $payment['payment_sum_declined']) {
                            //Если транзакция была, и ее сумма совпадает с суммой отвергнутой части - отменяем транзакцию
                            $cashBackTransaction->setStatus(TransactionStatusEnumType::STATUS_REJECT);
                            $this->em->persist($cashBackTransaction);
                            $this->em->flush();

//                            $this->getPushSender()->sendPush($cashBackTrek->getUser(), PushMessage::MESSAGE_CASHBACK_REJECTED);
                        } else {
                            //Сюда мы никогда не должны попасть
                            $this->logAndShow('Невыполнимое условие');
                        }
                    }
                }
            }
            $offset += $limit;
        } while ($paymentsCollection['_meta']['count'] > $offset);
    }

    /**
     * Геттер репозитория кешбеков.
     *
     * @return CashBackRepository
     */
    protected function getCashBackRepository(): CashBackRepository
    {
        return $this->em->getRepository(CashBack::class);
    }

    /**
     * Геттер репозитория отслеживаний кешбеков.
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCashBackTrekRepository()
    {
        return $this->em->getRepository(CashBackTrek::class);
    }

    /**
     * @param string $message
     */
    protected function logAndShow(string $message): void
    {
        echo $message.PHP_EOL;
        $this->logger->addInfo($message);
    }

    /**
     * @param CashBackTrek $cashBackTrek
     * @param float        $amount
     * @param string       $status
     *
     * @throws \Exception
     *
     * @return Transaction
     */
    protected function createCashBackTransaction(CashBackTrek $cashBackTrek, float $amount, string $status): Transaction
    {
        if (!isset(TransactionStatusEnumType::getChoices()[$status])) {
            throw new \Exception('No such status: '.$status);
        }

        $cashBackTransaction = $this->transactionManager->changeBalance($cashBackTrek->getUser(), $amount, TransactionEnumType::INCREASE, $status);

        $cashBackTrek->setTransaction($cashBackTransaction);
        $this->em->persist($cashBackTransaction);
        $this->em->flush();

        return $cashBackTransaction;
    }
}
