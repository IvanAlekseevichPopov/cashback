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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * GetCashBackAdmitadCommand.
 */
class GetCashBackAdmitadCommand extends ContainerAwareCommand
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
            ->setName('app:cashback:parse:admitad')
            ->setDescription('Берет новые кешбеки с адмитада');
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

//                if(6115 == $item['id']){
//                    dump($item);
//                    die();
//                }
//
//                continue;
//
                //Проверка, подходит ли нам кешбек
                if ($item['connected']) { //TODO remove
                    $this->logAndShow($item['id'].'.Already added');
                    continue;
                }

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

//    <?php
//array:40 [
//  "goto_cookie_lifetime" => 3
//  "rating" => "10.0"
//  "rate_of_approve" => "96"
//  "more_rules" => """
//    <p>Просим обратить Ваше внимание на следующие правила, в случае невыполнения которых Вы можете быть отключены от партнерской программы.</p>\r\n
//    \r\n
//    <p><span style="font-size:14px"><strong>Глобальные правила программы Aliexpress</strong></span></p>\r\n
//    \r\n
//    <p><strong>Виртуальные категории, такие как подарочные карты, купоны и сервисы услуг, книги для российских покупателей не оплачиваются. Cashback за подарочные сертификаты не начисляется.</strong></p>\r\n
//    \r\n
//    <p>Партнеры не должны:&nbsp;</p>\r\n
//    \r\n
//    <ol>\r\n
//    \t<li>Использовать любую форму обмена ресурсами для продвижения товара или услуги (например, использование вознаграждения или поощрения, чтобы мотивировать посетителей на выполнение определенного действия);&nbsp;</li>\r\n
//    \t<li>Побуждать посетителей Рекламного Сайта (вне зависимости от того, намеревались они или нет) регистрироваться, приобретать или пользоваться любыми продуктами и услугами Партнеров&nbsp;после того, как эти посетители кликают/переходят по рекламным объявлениям Партнера;&nbsp;</li>\r\n
//    \t<li>Использовать любые программы, скрипты или другие способы прямо или косвенно сгенерировать любой некачественный просмотр, клик или запрос рекламных объявлений;</li>\r\n
//    \t<li>Использовать для своего продвижения любой недобросовестный, злоумышленный, или просто некорректный метод, включая использование вирусов, ненастоящей интернет-страницы, &quot;угон&quot; браузеров, копирование заглавной страницы международной платформы AliExpress, подменяя информацию или контент зарегистрированных участников Международной Платформы AliExpress.com или сайта с помощью злоумышленных программных расширений, несанкционированного программного обеспечения или компьютерных программ, подменяя исходный код или параметры URL Международной Платформы AliExpress.com и его сайта, и т.д..</li>\r\n
//    \t<li>Использовать любую преувеличенную, ложную или вводящую в заблуждение информацию или контент, размещая рекламные объявления или ссылки (например &ldquo;покупаешь один товар, в подарок второй&rdquo;), чтобы продвигать любую страницу кампании Международной Платформы AliExpress.com&nbsp;на Рекламных Каналах&nbsp;с целью мотивировать посетителей переходить по рекламе;</li>\r\n
//    \t<li>Быть вовлеченным в любую незаконную деятельность;&nbsp;</li>\r\n
//    \t<li>Участвовать в любой деятельности, которая наносит какой-либо ущерб или неудобства AliExpress, ее филиалам или Международной Платформе AliExpress.com;&nbsp;</li>\r\n
//    \t<li>В любой форме использовать всплывающие окна (pop-up и pop-under)</li>\r\n
//    \t<li>Участвовать в любых кампаниях Поисковой оптимизации (SEO) или Поискового маркетинга (SEM), используя бренды Alibaba Group, включая&nbsp;<a href="http://alibaba.com/" target="_blank">alibaba.com</a>, Alibaba Group, Taobao, Tmall, Tmall Global, AliExpress, Alimama, AliCloud, Aliyun, eTao, Juhuasuan, и и т.д.;</li>\r\n
//    \t<li>Участвовать в любых мошеннических сделках, одному или вместе со своими партнерами (включая друзей, родственников, коллег, и т.д.), на международной платформе AliExpress или сайте. Например, Вы или Ваши партнеры, делаете покупки на Международной Платформе AliExpress по заранее подготовленным ссылкам и получаете комиссию через Партнерскую Программу AliExpress;</li>\r\n
//    \t<li>Использовать любые стимулы (уловки) для нечестного завлечения посетителей на рекламную страницу Международной Платформы AliExpress</li>\r\n
//    </ol>\r\n
//    \r\n
//    <p>Обратите внимание, что время жизни cookie - одна сессия.&nbsp;</p>\r\n
//    \r\n
//    <p>Уважаемые партнеры, хотим обратить Ваше внимание на то, что при обнаружении ваших обьявлений по брендовым запросам (алибаба, алиэкспресс, alibaba, aliexpress,&nbsp;alibaba.com, Alibaba Group, Taobao, Tmall, Tmall Global, AliExpress, Alimama, AliCloud, Aliyun, eTao, Juhuasuan а также схожих написаний или написаний с ошибками) - Вы будете безвозвратно отключены от данной партнерской программы без сохранения заработка. Если вы не включили в минус-слова вышеперечисленные брендовые запросы, и Вы, например, будете обнаружены по запросу &quot;скидки aliexpress&quot; - вас также коснутся данные санкции. Пожалуйста, включайте в минус-слова данные запросы, будьте внимательны.</p>\r\n
//    \r\n
//    <p><br />\r\n
//    <strong>В партнерской программе &quot;Aliexpress&quot; запрещены прямые Pop Up, Pop Under, Click Under и прочие виды агрессивного/PPV-трафика. Вариант использования данных видов трафика возможен только через страницу - прокладку, где пользователь должен совершить какое-либо действие, чтобы перейти на сайт Aliexpress</strong></p>\r\n
//    \r\n
//    <p>Все партнеры, которые будут замечены в использовании запрещенного вида трафика - будут незамедлительно отключены от партнерской программы без возможности сохранения заработка и повторного подключения.</p>\r\n
//    \r\n
//    <p>Также запрещается любое использование партнерских ссылок в сети Itao (ru.itao.com)</p>\r\n
//    \r\n
//    <p>Правилами партнерской программы Aliexpress INT запрещается призывать пользователей к возврату средств за заказ, оформлению рефандов и другим мошенническим действиям. Любой веб-мастер, размещающий на своей площадке призывы к &quot;refund&quot;, будет незамедлительно отключен от программы, его заказы будут отклонены в системе, а возобновление сотрудничества с программой будет невозможно. Рекламодатель также вправе списать с баланса вебмастера сумму уже оплаченной комиссии за подобный заказ.</p>\r\n
//    \r\n
//    <p>С 2018 года заказы, по которым был оформлен возврат, вне зависимости от причины возврата, не подлежат оплате. В случае если вознаграждение за такой заказ уже было выведено вебмастером, рекламодатель остается вправе&nbsp;списать эквивалентную сумму с баланса вебмастера.</p>\r\n
//    \r\n
//    <p>Пожалуйста, обратите внимание, что правилами партнерской программы строго запрещается промотировать &quot;скрытые&quot; товары на Aliexpress. В случае промотирования этих товаров вы будете отключены от программы без сохранения заработка.</p>\r\n
//    \r\n
//    <p>Максимальное вознаграждение за оплаченный заказ составляет $38.&nbsp;</p>\r\n
//    \r\n
//    <p>В виду того, что партнерская программа Aliexpress предоставляет лучшие условия для заработка, от Вас требуется соблюдение правил партнерской программы и предоставление качественного трафика. Предоставляемый Вами трафик будет оцениваться по уровню конверсии и объему продаж, а также по количеству новых клиентов, привлекаемых через Ваш источник трафика.</p>\r\n
//    """
//  "exclusive" => false
//  "image" => "http://cdn.admitad.com/campaign/images/2015/05/12/a81cc17e95c570a41c661fb1aa6c6439.jpg"
//  "actions" => array:21 [
//    0 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Interior accessories NEW"
//      "id" => 13440
//    ]
//    1 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Garden supplies NEW"
//      "id" => 13441
//    ]
//    2 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Women's clothing NEW"
//      "id" => 13442
//    ]
//    3 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Men's clothing NEW"
//      "id" => 13443
//    ]
//    4 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Children's clothing NEW"
//      "id" => 13444
//    ]
//    5 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "5.40%"
//      "type" => "sale"
//      "name" => "All other categories NEW"
//      "id" => 13445
//    ]
//    6 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "3%-4%"
//      "type" => "sale"
//      "name" => "Телефоны и Телекоммуникация"
//      "id" => 9335
//    ]
//    7 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2%"
//      "type" => "sale"
//      "name" => "Non-affiliated products"
//      "id" => 10735
//    ]
//    8 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "3%-69%"
//      "type" => "sale"
//      "name" => "Hot products"
//      "id" => 10736
//    ]
//    9 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6%-10%"
//      "type" => "sale"
//      "name" => "Уход за волосами и Аксессуары"
//      "id" => 9336
//    ]
//    10 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "External storage NEW"
//      "id" => 13437
//    ]
//    11 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6%-10%"
//      "type" => "sale"
//      "name" => "Остальные товары из категории Телекоммуникации"
//      "id" => 9338
//    ]
//    12 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Mobile phone NEW"
//      "id" => 13431
//    ]
//    13 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Computer peripherals NEW"
//      "id" => 13432
//    ]
//    14 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Tablets NEW"
//      "id" => 13433
//    ]
//    15 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Desktop NEW"
//      "id" => 13434
//    ]
//    16 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Laptop netbooks NEW"
//      "id" => 13435
//    ]
//    17 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Home audio& video equipment NEW"
//      "id" => 13436
//    ]
//    18 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6%-10%"
//      "type" => "sale"
//      "name" => "Другие категории"
//      "id" => 9337
//    ]
//    19 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "2.30%"
//      "type" => "sale"
//      "name" => "Internal storage NEW"
//      "id" => 13438
//    ]
//    20 => array:5 [
//      "hold_time" => 0
//      "payment_size" => "6.90%"
//      "type" => "sale"
//      "name" => "Mobile phone accessories NEW"
//      "id" => 13439
//    ]
//  ]
//  "avg_money_transfer_time" => 38
//  "currency" => "USD"
//  "name_aliases" => "алиэкспресс, али экспресс, алиэскпрес, ali expres, ali express, aliexpres, aliexpress, фдшучзкуыы, фдш учзкуыы, int, инт, ИНТ, INT, tmall, nvjkk, тмолл, алиекспресс, али експресс, али, WW"
//  "activation_date" => "2013-12-02T17:06:31"
//  "cr" => 11.28
//  "ecpc" => 0.08
//  "id" => 6115
//  "individual_terms" => false
//  "modified_date" => "2018-10-12T20:27:25"
//  "avg_hold_time" => 38
//  "site_url" => "http://aliexpress.com/"
//  "regions" => array:242 [
//    0 => array:1 [
//      "region" => "AD"
//    ]
//    1 => array:1 [
//      "region" => "AE"
//    ]
//    2 => array:1 [
//      "region" => "AF"
//    ]
//    3 => array:1 [
//      "region" => "AG"
//    ]
//    4 => array:1 [
//      "region" => "AI"
//    ]
//    5 => array:1 [
//      "region" => "AL"
//    ]
//    6 => array:1 [
//      "region" => "AM"
//    ]
//    7 => array:1 [
//      "region" => "AO"
//    ]
//    8 => array:1 [
//      "region" => "AQ"
//    ]
//    9 => array:1 [
//      "region" => "AR"
//    ]
//    10 => array:1 [
//      "region" => "AS"
//    ]
//    11 => array:1 [
//      "region" => "AT"
//    ]
//    12 => array:1 [
//      "region" => "AU"
//    ]
//    13 => array:1 [
//      "region" => "AW"
//    ]
//    14 => array:1 [
//      "region" => "AX"
//    ]
//    15 => array:1 [
//      "region" => "AZ"
//    ]
//    16 => array:1 [
//      "region" => "BA"
//    ]
//    17 => array:1 [
//      "region" => "BB"
//    ]
//    18 => array:1 [
//      "region" => "BD"
//    ]
//    19 => array:1 [
//      "region" => "BE"
//    ]
//    20 => array:1 [
//      "region" => "BF"
//    ]
//    21 => array:1 [
//      "region" => "BG"
//    ]
//    22 => array:1 [
//      "region" => "BH"
//    ]
//    23 => array:1 [
//      "region" => "BI"
//    ]
//    24 => array:1 [
//      "region" => "BJ"
//    ]
//    25 => array:1 [
//      "region" => "BM"
//    ]
//    26 => array:1 [
//      "region" => "BN"
//    ]
//    27 => array:1 [
//      "region" => "BO"
//    ]
//    28 => array:1 [
//      "region" => "BR"
//    ]
//    29 => array:1 [
//      "region" => "BS"
//    ]
//    30 => array:1 [
//      "region" => "BT"
//    ]
//    31 => array:1 [
//      "region" => "BV"
//    ]
//    32 => array:1 [
//      "region" => "BW"
//    ]
//    33 => array:1 [
//      "region" => "BY"
//    ]
//    34 => array:1 [
//      "region" => "BZ"
//    ]
//    35 => array:1 [
//      "region" => "CA"
//    ]
//    36 => array:1 [
//      "region" => "CC"
//    ]
//    37 => array:1 [
//      "region" => "CD"
//    ]
//    38 => array:1 [
//      "region" => "CF"
//    ]
//    39 => array:1 [
//      "region" => "CG"
//    ]
//    40 => array:1 [
//      "region" => "CH"
//    ]
//    41 => array:1 [
//      "region" => "CI"
//    ]
//    42 => array:1 [
//      "region" => "CK"
//    ]
//    43 => array:1 [
//      "region" => "CL"
//    ]
//    44 => array:1 [
//      "region" => "CM"
//    ]
//    45 => array:1 [
//      "region" => "CN"
//    ]
//    46 => array:1 [
//      "region" => "CO"
//    ]
//    47 => array:1 [
//      "region" => "CR"
//    ]
//    48 => array:1 [
//      "region" => "CU"
//    ]
//    49 => array:1 [
//      "region" => "CV"
//    ]
//    50 => array:1 [
//      "region" => "CW"
//    ]
//    51 => array:1 [
//      "region" => "CX"
//    ]
//    52 => array:1 [
//      "region" => "CY"
//    ]
//    53 => array:1 [
//      "region" => "CZ"
//    ]
//    54 => array:1 [
//      "region" => "DE"
//    ]
//    55 => array:1 [
//      "region" => "DJ"
//    ]
//    56 => array:1 [
//      "region" => "DK"
//    ]
//    57 => array:1 [
//      "region" => "DM"
//    ]
//    58 => array:1 [
//      "region" => "DO"
//    ]
//    59 => array:1 [
//      "region" => "DZ"
//    ]
//    60 => array:1 [
//      "region" => "EC"
//    ]
//    61 => array:1 [
//      "region" => "EE"
//    ]
//    62 => array:1 [
//      "region" => "EG"
//    ]
//    63 => array:1 [
//      "region" => "EH"
//    ]
//    64 => array:1 [
//      "region" => "ER"
//    ]
//    65 => array:1 [
//      "region" => "ES"
//    ]
//    66 => array:1 [
//      "region" => "ET"
//    ]
//    67 => array:1 [
//      "region" => "FI"
//    ]
//    68 => array:1 [
//      "region" => "FJ"
//    ]
//    69 => array:1 [
//      "region" => "FK"
//    ]
//    70 => array:1 [
//      "region" => "FM"
//    ]
//    71 => array:1 [
//      "region" => "FO"
//    ]
//    72 => array:1 [
//      "region" => "FR"
//    ]
//    73 => array:1 [
//      "region" => "GA"
//    ]
//    74 => array:1 [
//      "region" => "GB"
//    ]
//    75 => array:1 [
//      "region" => "GD"
//    ]
//    76 => array:1 [
//      "region" => "GE"
//    ]
//    77 => array:1 [
//      "region" => "GF"
//    ]
//    78 => array:1 [
//      "region" => "GG"
//    ]
//    79 => array:1 [
//      "region" => "GH"
//    ]
//    80 => array:1 [
//      "region" => "GI"
//    ]
//    81 => array:1 [
//      "region" => "GL"
//    ]
//    82 => array:1 [
//      "region" => "GM"
//    ]
//    83 => array:1 [
//      "region" => "GN"
//    ]
//    84 => array:1 [
//      "region" => "GP"
//    ]
//    85 => array:1 [
//      "region" => "GQ"
//    ]
//    86 => array:1 [
//      "region" => "GR"
//    ]
//    87 => array:1 [
//      "region" => "GS"
//    ]
//    88 => array:1 [
//      "region" => "GT"
//    ]
//    89 => array:1 [
//      "region" => "GU"
//    ]
//    90 => array:1 [
//      "region" => "GW"
//    ]
//    91 => array:1 [
//      "region" => "GY"
//    ]
//    92 => array:1 [
//      "region" => "HK"
//    ]
//    93 => array:1 [
//      "region" => "HM"
//    ]
//    94 => array:1 [
//      "region" => "HN"
//    ]
//    95 => array:1 [
//      "region" => "HR"
//    ]
//    96 => array:1 [
//      "region" => "HT"
//    ]
//    97 => array:1 [
//      "region" => "HU"
//    ]
//    98 => array:1 [
//      "region" => "ID"
//    ]
//    99 => array:1 [
//      "region" => "IE"
//    ]
//    100 => array:1 [
//      "region" => "IL"
//    ]
//    101 => array:1 [
//      "region" => "IM"
//    ]
//    102 => array:1 [
//      "region" => "IO"
//    ]
//    103 => array:1 [
//      "region" => "IQ"
//    ]
//    104 => array:1 [
//      "region" => "IR"
//    ]
//    105 => array:1 [
//      "region" => "IS"
//    ]
//    106 => array:1 [
//      "region" => "IT"
//    ]
//    107 => array:1 [
//      "region" => "JE"
//    ]
//    108 => array:1 [
//      "region" => "JM"
//    ]
//    109 => array:1 [
//      "region" => "JO"
//    ]
//    110 => array:1 [
//      "region" => "JP"
//    ]
//    111 => array:1 [
//      "region" => "KE"
//    ]
//    112 => array:1 [
//      "region" => "KG"
//    ]
//    113 => array:1 [
//      "region" => "KH"
//    ]
//    114 => array:1 [
//      "region" => "KI"
//    ]
//    115 => array:1 [
//      "region" => "KM"
//    ]
//    116 => array:1 [
//      "region" => "KN"
//    ]
//    117 => array:1 [
//      "region" => "KP"
//    ]
//    118 => array:1 [
//      "region" => "KR"
//    ]
//    119 => array:1 [
//      "region" => "KW"
//    ]
//    120 => array:1 [
//      "region" => "KY"
//    ]
//    121 => array:1 [
//      "region" => "KZ"
//    ]
//    122 => array:1 [
//      "region" => "LA"
//    ]
//    123 => array:1 [
//      "region" => "LB"
//    ]
//    124 => array:1 [
//      "region" => "LC"
//    ]
//    125 => array:1 [
//      "region" => "LI"
//    ]
//    126 => array:1 [
//      "region" => "LK"
//    ]
//    127 => array:1 [
//      "region" => "LR"
//    ]
//    128 => array:1 [
//      "region" => "LS"
//    ]
//    129 => array:1 [
//      "region" => "LT"
//    ]
//    130 => array:1 [
//      "region" => "LU"
//    ]
//    131 => array:1 [
//      "region" => "LV"
//    ]
//    132 => array:1 [
//      "region" => "LY"
//    ]
//    133 => array:1 [
//      "region" => "MA"
//    ]
//    134 => array:1 [
//      "region" => "MC"
//    ]
//    135 => array:1 [
//      "region" => "MD"
//    ]
//    136 => array:1 [
//      "region" => "ME"
//    ]
//    137 => array:1 [
//      "region" => "MG"
//    ]
//    138 => array:1 [
//      "region" => "MH"
//    ]
//    139 => array:1 [
//      "region" => "MK"
//    ]
//    140 => array:1 [
//      "region" => "ML"
//    ]
//    141 => array:1 [
//      "region" => "MM"
//    ]
//    142 => array:1 [
//      "region" => "MN"
//    ]
//    143 => array:1 [
//      "region" => "MO"
//    ]
//    144 => array:1 [
//      "region" => "MP"
//    ]
//    145 => array:1 [
//      "region" => "MQ"
//    ]
//    146 => array:1 [
//      "region" => "MR"
//    ]
//    147 => array:1 [
//      "region" => "MS"
//    ]
//    148 => array:1 [
//      "region" => "MT"
//    ]
//    149 => array:1 [
//      "region" => "MU"
//    ]
//    150 => array:1 [
//      "region" => "MV"
//    ]
//    151 => array:1 [
//      "region" => "MW"
//    ]
//    152 => array:1 [
//      "region" => "MX"
//    ]
//    153 => array:1 [
//      "region" => "MY"
//    ]
//    154 => array:1 [
//      "region" => "MZ"
//    ]
//    155 => array:1 [
//      "region" => "NA"
//    ]
//    156 => array:1 [
//      "region" => "NC"
//    ]
//    157 => array:1 [
//      "region" => "NE"
//    ]
//    158 => array:1 [
//      "region" => "NF"
//    ]
//    159 => array:1 [
//      "region" => "NG"
//    ]
//    160 => array:1 [
//      "region" => "NI"
//    ]
//    161 => array:1 [
//      "region" => "NL"
//    ]
//    162 => array:1 [
//      "region" => "NO"
//    ]
//    163 => array:1 [
//      "region" => "NP"
//    ]
//    164 => array:1 [
//      "region" => "NR"
//    ]
//    165 => array:1 [
//      "region" => "NU"
//    ]
//    166 => array:1 [
//      "region" => "NZ"
//    ]
//    167 => array:1 [
//      "region" => "OM"
//    ]
//    168 => array:1 [
//      "region" => "PA"
//    ]
//    169 => array:1 [
//      "region" => "PE"
//    ]
//    170 => array:1 [
//      "region" => "PF"
//    ]
//    171 => array:1 [
//      "region" => "PG"
//    ]
//    172 => array:1 [
//      "region" => "PH"
//    ]
//    173 => array:1 [
//      "region" => "PK"
//    ]
//    174 => array:1 [
//      "region" => "PL"
//    ]
//    175 => array:1 [
//      "region" => "PM"
//    ]
//    176 => array:1 [
//      "region" => "PN"
//    ]
//    177 => array:1 [
//      "region" => "PR"
//    ]
//    178 => array:1 [
//      "region" => "PS"
//    ]
//    179 => array:1 [
//      "region" => "PT"
//    ]
//    180 => array:1 [
//      "region" => "PW"
//    ]
//    181 => array:1 [
//      "region" => "PY"
//    ]
//    182 => array:1 [
//      "region" => "QA"
//    ]
//    183 => array:1 [
//      "region" => "RE"
//    ]
//    184 => array:1 [
//      "region" => "RO"
//    ]
//    185 => array:1 [
//      "region" => "RS"
//    ]
//    186 => array:1 [
//      "region" => "RU"
//    ]
//    187 => array:1 [
//      "region" => "RW"
//    ]
//    188 => array:1 [
//      "region" => "SA"
//    ]
//    189 => array:1 [
//      "region" => "SB"
//    ]
//    190 => array:1 [
//      "region" => "SC"
//    ]
//    191 => array:1 [
//      "region" => "SD"
//    ]
//    192 => array:1 [
//      "region" => "SE"
//    ]
//    193 => array:1 [
//      "region" => "SG"
//    ]
//    194 => array:1 [
//      "region" => "SH"
//    ]
//    195 => array:1 [
//      "region" => "SI"
//    ]
//    196 => array:1 [
//      "region" => "SJ"
//    ]
//    197 => array:1 [
//      "region" => "SK"
//    ]
//    198 => array:1 [
//      "region" => "SL"
//    ]
//    199 => array:1 [
//      "region" => "SM"
//    ]
//    200 => array:1 [
//      "region" => "SN"
//    ]
//    201 => array:1 [
//      "region" => "SO"
//    ]
//    202 => array:1 [
//      "region" => "SR"
//    ]
//    203 => array:1 [
//      "region" => "ST"
//    ]
//    204 => array:1 [
//      "region" => "SV"
//    ]
//    205 => array:1 [
//      "region" => "SY"
//    ]
//    206 => array:1 [
//      "region" => "SZ"
//    ]
//    207 => array:1 [
//      "region" => "TC"
//    ]
//    208 => array:1 [
//      "region" => "TD"
//    ]
//    209 => array:1 [
//      "region" => "TF"
//    ]
//    210 => array:1 [
//      "region" => "TG"
//    ]
//    211 => array:1 [
//      "region" => "TH"
//    ]
//    212 => array:1 [
//      "region" => "TJ"
//    ]
//    213 => array:1 [
//      "region" => "TK"
//    ]
//    214 => array:1 [
//      "region" => "TL"
//    ]
//    215 => array:1 [
//      "region" => "TM"
//    ]
//    216 => array:1 [
//      "region" => "TN"
//    ]
//    217 => array:1 [
//      "region" => "TO"
//    ]
//    218 => array:1 [
//      "region" => "TT"
//    ]
//    219 => array:1 [
//      "region" => "TV"
//    ]
//    220 => array:1 [
//      "region" => "TW"
//    ]
//    221 => array:1 [
//      "region" => "TZ"
//    ]
//    222 => array:1 [
//      "region" => "UA"
//    ]
//    223 => array:1 [
//      "region" => "UG"
//    ]
//    224 => array:1 [
//      "region" => "UM"
//    ]
//    225 => array:1 [
//      "region" => "US"
//    ]
//    226 => array:1 [
//      "region" => "UY"
//    ]
//    227 => array:1 [
//      "region" => "UZ"
//    ]
//    228 => array:1 [
//      "region" => "VA"
//    ]
//    229 => array:1 [
//      "region" => "VC"
//    ]
//    230 => array:1 [
//      "region" => "VE"
//    ]
//    231 => array:1 [
//      "region" => "VG"
//    ]
//    232 => array:1 [
//      "region" => "VI"
//    ]
//    233 => array:1 [
//      "region" => "VN"
//    ]
//    234 => array:1 [
//      "region" => "VU"
//    ]
//    235 => array:1 [
//      "region" => "WF"
//    ]
//    236 => array:1 [
//      "region" => "WS"
//    ]
//    237 => array:1 [
//      "region" => "YE"
//    ]
//    238 => array:1 [
//      "region" => "YT"
//    ]
//    239 => array:1 [
//      "region" => "ZA"
//    ]
//    240 => array:1 [
//      "region" => "ZM"
//    ]
//    241 => array:1 [
//      "region" => "ZW"
//    ]
//  ]
//  "landing_title" => null
//  "geotargeting" => true
//  "status" => "active"
//  "coupon_iframe_denied" => false
//  "traffics" => array:12 [
//    0 => array:3 [
//      "enabled" => true
//      "name" => "Cashback"
//      "id" => 1
//    ]
//    1 => array:3 [
//      "enabled" => false
//      "name" => "PopUp / ClickUnder"
//      "id" => 2
//    ]
//    2 => array:3 [
//      "enabled" => true
//      "name" => "Контекстная реклама"
//      "id" => 3
//    ]
//    3 => array:3 [
//      "enabled" => true
//      "name" => "Дорвей - трафик"
//      "id" => 4
//    ]
//    4 => array:3 [
//      "enabled" => true
//      "name" => "Email - рассылка"
//      "id" => 5
//    ]
//    5 => array:3 [
//      "enabled" => false
//      "name" => "Контекстная реклама на Бренд"
//      "id" => 6
//    ]
//    6 => array:3 [
//      "enabled" => true
//      "name" => "Реклама в социальных сетях"
//      "id" => 7
//    ]
//    7 => array:3 [
//      "enabled" => false
//      "name" => "Мотивированный трафик"
//      "id" => 8
//    ]
//    8 => array:3 [
//      "enabled" => false
//      "name" => "Toolbar"
//      "id" => 9
//    ]
//    9 => array:3 [
//      "enabled" => false
//      "name" => "Adult - трафик"
//      "id" => 14
//    ]
//    10 => array:3 [
//      "enabled" => true
//      "name" => "Тизерные сети"
//      "id" => 18
//    ]
//    11 => array:3 [
//      "enabled" => true
//      "name" => "Youtube Канал"
//      "id" => 19
//    ]
//  ]
//  "description" => """
//    Aliexpress &ndash; один из крупнейших мировых маркетплейсов, предлагающий клиентам самые низкие цены, а также выбор из более чем 100 миллионов товаров от 200 тысяч продавцов, 20 самых популярных способов оплаты и доставку в более чем 200 стран.\r\n
//    \r\n
//    Преимущества для веб-мастеров:\r\n
//    \r\n
//    \r\n
//    \tПроцент подтверждения более 90%\r\n
//    \tМировая известность бренда\r\n
//    \tНет ограничений по ГЕО\r\n
//    \tБесплатная/дешевая доставка\r\n
//    \tПринимаются все основные виды оплат\r\n
//    \tОплачиваются все товары\r\n
//    \tВысокие тарифы\r\n
//    \r\n
//    \r\n
//    Преимущества для покупателей:\r\n
//    \r\n
//    \r\n
//    \tБолее 100 млн. товаров\r\n
//    \tБолее 200 тыс. продавцов\r\n
//    \tНизкие цены\r\n
//    \tБесплатная доставка практически на все товары\r\n
//    \tБолее 20 способов оплаты\r\n
//    \tБезопасные переводы: продавец получает деньги только после подтверждения заказа\r\n
//    \tКлиентская поддержка 24/7\r\n
//    \tМультиязычные сайты Aliexpress\r\n
//    \r\n
//    \r\n
//    Тарифы\r\n
//    \r\n
//    С 10 октября 2018 года разделение по ГЕО отменяется и в силу вступают следующие условия:\r\n
//    \r\n
//    \r\n
//    \t\r\n
//    \t\t\r\n
//    \t\t\tКатегория\r\n
//    \t\t\tСтавка\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tМобильные телефоны\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tКомпьютерная периферия\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tПланшеты\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tКомпьютеры (десктоп)\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tНоутбуки\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tАудио- и видеооборудование\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tУстройства внешнего хранения информации\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tУстройства внутреннего хранения информации\r\n
//    \t\t\t2,3%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tАксессуары для мобильных телефонов\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tТовары для дома\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tТовары для сада\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tЖенская одежда\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tМужская одежда\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tДетская одежда\r\n
//    \t\t\t6,9%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tВсе остальные категории\r\n
//    \t\t\t5,4%\r\n
//    \t\t\r\n
//    \t\t\r\n
//    \t\t\tСпециальные и виртуальные категории*\r\n
//    \t\t\t0%\r\n
//    \t\t\r\n
//    \t\r\n
//    \r\n
//    \r\n
//    *all virtual products covered by special category, travel and coupon services or books are NOT subject to commission. Such virtual products include but not limited to gift cards and coupons. We reserve the right, in our sole and absolute discretion, to determine whether a product is regarded as a virtual product for the purpose of calculating commission.\r\n
//    \r\n
//    Hot Products\r\n
//    \r\n
//    Основное правило начисления вознаграждения за Hot Products: переход должен быть осуществлен по рекламному материалу, ведущему на Hot Product. В случае если переход был осуществлен по основной ссылке (ru.aliexpress.com, aliexpress.com) или по ссылке на обычный товар, будет применено стандартное вознаграждение.\r\n
//    \r\n
//    *Более подробно ознакомиться с&nbsp;инструментом Hot Products вы можете в&nbsp;центре помощи.\r\n
//    \r\n
//    Обратите внимание: с 1 августа 2018 года Tmall стал отдельный офером! Найти его вы можете в каталоге партнерских програм. Заказы Tmall,&nbsp;совершенные&nbsp;после&nbsp;31&nbsp;июля, будут оплачиваться только через новую программу, поэтому просим вас незамедлительно подключиться к новому оферу и вести трафик на&nbsp;Tmall только через него.\r\n
//    \r\n
//    Обратите внимание: все товары на&nbsp;https://t.aliexpress.com/ru&nbsp;являются неаффилиатными. В связи с этим просим вас не направлять траффик на данный лендинг.\r\n
//    \r\n
//    C 10 октября 2018 года все товары на сайте Aliexpress являются аффилиатными.\r\n
//    \r\n
//    Новости партнерской программы вы можете найти в нашей группе Вконтакте.\r\n
//    \r\n
//    Более подробно ознакомиться с информацией об&nbsp;Антиспам отчетах от Aliexpress вы можете ознакомиться здесь.\r\n
//    \r\n
//    Возможные сценарии трекинга заказов вы можете найти здесь.
//    """
//  "cr_trend" => "0.0100"
//  "raw_description" => """
//    <p>Aliexpress &ndash; один из крупнейших мировых маркетплейсов, предлагающий клиентам самые низкие цены, а также выбор из более чем 100 миллионов товаров от 200 тысяч продавцов, 20 самых популярных способов оплаты и доставку в более чем 200 стран.</p>\r\n
//    \r\n
//    <p><span style="font-size:14px"><strong>Преимущества для веб-мастеров:</strong></span></p>\r\n
//    \r\n
//    <ul>\r\n
//    \t<li>Процент подтверждения более 90%</li>\r\n
//    \t<li>Мировая известность бренда</li>\r\n
//    \t<li>Нет ограничений по ГЕО</li>\r\n
//    \t<li>Бесплатная/дешевая доставка</li>\r\n
//    \t<li>Принимаются все основные виды оплат</li>\r\n
//    \t<li>Оплачиваются все товары</li>\r\n
//    \t<li>Высокие тарифы</li>\r\n
//    </ul>\r\n
//    \r\n
//    <p><span style="font-size:14px"><strong>Преимущества для покупателей:</strong></span></p>\r\n
//    \r\n
//    <ul>\r\n
//    \t<li>Более 100 млн. товаров</li>\r\n
//    \t<li>Более 200 тыс. продавцов</li>\r\n
//    \t<li>Низкие цены</li>\r\n
//    \t<li>Бесплатная доставка практически на все товары</li>\r\n
//    \t<li>Более 20 способов оплаты</li>\r\n
//    \t<li>Безопасные переводы: продавец получает деньги только после подтверждения <strong>заказа</strong></li>\r\n
//    \t<li>Клиентская поддержка 24/7</li>\r\n
//    \t<li>Мультиязычные сайты Aliexpress</li>\r\n
//    </ul>\r\n
//    \r\n
//    <p><span style="font-size:14px"><strong>Тарифы</strong></span></p>\r\n
//    \r\n
//    <p><strong>С 10 октября 2018 года разделение по ГЕО отменяется и в силу вступают следующие условия:</strong></p>\r\n
//    \r\n
//    <table border="1" cellpadding="1" cellspacing="1" style="width:400px">\r\n
//    \t<tbody>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td><span style="font-size:14px"><strong>Категория</strong></span></td>\r\n
//    \t\t\t<td><span style="font-size:14px"><strong>Ставка</strong></span></td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Мобильные телефоны</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Компьютерная периферия</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Планшеты</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Компьютеры (десктоп)</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Ноутбуки</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Аудио- и видеооборудование</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Устройства внешнего хранения информации</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Устройства внутреннего хранения информации</td>\r\n
//    \t\t\t<td>2,3%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Аксессуары для мобильных телефонов</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Товары для дома</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Товары для сада</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Женская одежда</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Мужская одежда</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Детская одежда</td>\r\n
//    \t\t\t<td>6,9%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Все остальные категории</td>\r\n
//    \t\t\t<td>5,4%</td>\r\n
//    \t\t</tr>\r\n
//    \t\t<tr>\r\n
//    \t\t\t<td>Специальные и виртуальные категории*</td>\r\n
//    \t\t\t<td>0%</td>\r\n
//    \t\t</tr>\r\n
//    \t</tbody>\r\n
//    </table>\r\n
//    \r\n
//    <p>*all virtual products covered by special category, travel and coupon services or books are NOT subject to commission. Such virtual products include but not limited to gift cards and coupons. We reserve the right, in our sole and absolute discretion, to determine whether a product is regarded as a virtual product for the purpose of calculating commission.</p>\r\n
//    \r\n
//    <p><span style="font-size:14px"><strong>Hot Products</strong></span></p>\r\n
//    \r\n
//    <p>Основное правило начисления вознаграждения за Hot Products: переход должен быть осуществлен по рекламному материалу, ведущему на Hot Product. В случае если переход был осуществлен по основной ссылке (ru.aliexpress.com, aliexpress.com) или по ссылке на обычный товар, будет применено стандартное вознаграждение.</p>\r\n
//    \r\n
//    <p>*Более подробно ознакомиться с&nbsp;инструментом Hot Products вы можете в&nbsp;<a href="https://help.admitad.com/ru/topic/193-hot-products/" target="_blank">центре помощи.</a></p>\r\n
//    \r\n
//    <p><strong>Обратите внимание: </strong>с 1 августа 2018 года Tmall стал отдельный офером! Найти его вы можете в каталоге партнерских програм. Заказы Tmall,&nbsp;совершенные&nbsp;после&nbsp;31&nbsp;июля, будут оплачиваться только через новую программу, поэтому просим вас незамедлительно подключиться к новому оферу и вести трафик на&nbsp;Tmall только через него.<br />\r\n
//    <br />\r\n
//    <strong>Обратите внимание: </strong>все товары на&nbsp;<a href="https://t.aliexpress.com/ru">https://t.aliexpress.com/ru</a>&nbsp;являются неаффилиатными. В связи с этим просим вас не направлять траффик на данный лендинг.</p>\r\n
//    \r\n
//    <p><strong>C 10 октября 2018 года все товары на сайте </strong><strong>Aliexpress </strong><strong>являются аффилиатными.</strong></p>\r\n
//    \r\n
//    <p><strong>Новости партнерской программы</strong> вы можете найти в нашей группе <a href="https://vk.com/aetmalladmitad" target="_blank">Вконтакте</a>.</p>\r\n
//    \r\n
//    <p>Более подробно ознакомиться с информацией об&nbsp;Антиспам отчетах от Aliexpress вы можете ознакомиться <a href="https://hq.admitad.com/public/storage/2018/09/13/Aliexpress_Antispam_Report_RU.pdf" target="_blank">здесь</a>.</p>\r\n
//    \r\n
//    <p>Возможные сценарии трекинга заказов вы можете найти <a href="https://hq.admitad.com/public/storage/2018/09/13/Orders_AE_RU.pdf" target="_blank">здесь</a>.</p>\r\n
//    """
//  "denynewwms" => false
//  "connected" => true
//  "max_hold_time" => null
//  "categories" => array:14 [
//    0 => array:4 [
//      "language" => "ru"
//      "id" => 62
//      "parent" => null
//      "name" => "Интернет-магазины"
//    ]
//    1 => array:4 [
//      "language" => "ru"
//      "id" => 64
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Одежда & Обувь"
//    ]
//    2 => array:4 [
//      "language" => "ru"
//      "id" => 65
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Цифровая & Бытовая техника"
//    ]
//    3 => array:4 [
//      "language" => "ru"
//      "id" => 66
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Мебель & Товары для дома"
//    ]
//    4 => array:4 [
//      "language" => "ru"
//      "id" => 67
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Красота & Здоровье"
//    ]
//    5 => array:4 [
//      "language" => "ru"
//      "id" => 69
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Товары для детей"
//    ]
//    6 => array:4 [
//      "language" => "ru"
//      "id" => 71
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Аксессуары"
//    ]
//    7 => array:4 [
//      "language" => "ru"
//      "id" => 72
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Подарки & Цветы"
//    ]
//    8 => array:4 [
//      "language" => "ru"
//      "id" => 85
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Спорт"
//    ]
//    9 => array:4 [
//      "language" => "ru"
//      "id" => 89
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Товары для творчества"
//    ]
//    10 => array:4 [
//      "language" => "ru"
//      "id" => 92
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Автотовары"
//    ]
//    11 => array:4 [
//      "language" => "ru"
//      "id" => 96
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Товары из Китая"
//    ]
//    12 => array:4 [
//      "language" => "ru"
//      "id" => 100
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Музыка & Звук"
//    ]
//    13 => array:4 [
//      "language" => "ru"
//      "id" => 102
//      "parent" => array:4 [
//        "language" => "ru"
//        "id" => 62
//        "parent" => null
//        "name" => "Интернет-магазины"
//      ]
//      "name" => "Инструменты & Садовая техника"
//    ]
//  ]
//  "retag" => false
//  "name" => "Aliexpress WW"
//  "landing_code" => null
//  "ecpc_trend" => "0.0000"
//  "epc_trend" => "0.0000"
//  "action_type" => "sale"
//  "epc" => 8.0
//  "allow_deeplink" => true
//  "show_products_links" => false
//]
}

