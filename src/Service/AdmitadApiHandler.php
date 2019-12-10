<?php

declare(strict_types=1);

namespace App\Service;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackImage;
use App\Entity\CashBackPlatform;
use Cocur\Slugify\Slugify;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;

class AdmitadApiHandler
{
    use ContainerAwareTrait;

    public const TIME_APPROXIMATION = '-1 hour';
    public const WRONG_PLATFORM_ID = 'Id переданной платформы не совпадает с константным';
    public const CONNECTION_STATUS_ACTIVE = 'active';
    public const POST_METHOD = 'POST';
    public const GET_METHOD = 'GET';
    public const TMP_FILE = '/tmp/asdfasdfasdfasdfasd';
    public const DATE_FORMAT = 'd.m.Y';
    public const OLDEST_PAYMENT_CHECK = '-70 days';
    public const NEWEST_PAYMENT_CHECK = '-2 days';
    public const ADMITAD_MESSAGE_NOT_FOUND = 'Not Found';

    /** @var LoggerInterface */
    protected $logger;

    /** @var EntityManagerInterface */
    protected $manager;

    /** Самая ранняя дата для поиска выплат по кешбекам */
    private $startDate;

    /** Самая поздняя дата для поиска выплат по кешбекам */
    private $endDate;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $manager)
    {
        $this->logger = $logger;
        $this->manager = $manager;
    }

    /**
     * Возвращает внутренний статус по строковому представлению внешнего статуса.
     *
     * @param array $admitadResponse
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getStatus(array $admitadResponse): string
    {
        switch ($admitadResponse['connection_status']) {
            case 'active':
                return CashBackStatusEnumType::APPROVED_PARTNERSHIP;
            case 'pending':
                return CashBackStatusEnumType::AWAITING_PARTNERSHIP;
            case 'declined':
                return CashBackStatusEnumType::REJECTED_PARTNERSHIP;
            default:
                throw new \Exception('unknown status - '.$admitadResponse['connection_status']);
        }
    }

//    /**
//     * Запрос авторизационного токена у Admitad.
//     *
//     * @param CashBackPlatform $admitadCashBackPlatform
//     *
//     * @throws \Exception
//     *
//     * @return array|null
//     */
//    public function getAccessToken(CashBackPlatform $admitadCashBackPlatform): ?array
//    {
//        $this->checkPlatformId($admitadCashBackPlatform);
//
//        $ch = curl_init();
//
//        curl_setopt($ch, CURLOPT_URL, $admitadCashBackPlatform->getBaseUrl().'token/');
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.$admitadCashBackPlatform->getAuthHeader()]);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, [
//            'grant_type' => 'client_credentials',
//            'client_id' => $admitadCashBackPlatform->getClientId(),
//            'scope' => 'advcampaigns arecords banners websites advcampaigns_for_website manage_advcampaigns statistics deeplink_generator',
//        ]);
//
//        $data = curl_exec($ch);
//        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//
//        curl_close($ch);
//        if (Response::HTTP_OK !== $httpcode) {
//            throw new \Exception('Invalid response api response: '.$data);
//        }
//
//        return json_decode($data, true);
//    }

//    /**
//     * Обновление временного токена авторизации адмитада.
//     *
//     * @param CashBackPlatform $admitadPlatform
//     */
//    public function updateAccessToken(CashBackPlatform $admitadPlatform): void
//    {
//        $now = new DateTimeImmutable(self::TIME_APPROXIMATION);
//
//        $token = $admitadPlatform->getToken();
//        $expiredAt = $admitadPlatform->getExpiredAt();
//        if (empty($token) || $expiredAt < $now) {
//            $tokenJson = $this->getAccessToken($admitadPlatform);
//
//            $admitadPlatform->setToken($tokenJson['access_token']);
//            $admitadPlatform->setExpiredAt($now->add(new DateInterval('PT'.$tokenJson['expires_in'].'S')));
//
//            $this->manager->persist($admitadPlatform);
//            $this->manager->flush();
//        }
//    }

    /**
     * Проверяет статус выбранной компании.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param CashBack         $cashBack
     *
     * @throws \Exception
     *
     * @return array|null
     *
     * @internal param $cashBackPlatform
     */
    public function checkCampaign(CashBackPlatform $admitadPlatform, CashBack $cashBack): ?array
    {
        if (null === $cashBack->getExternalId()) {
            return null;
        }

//        $this->updateAccessToken($admitadPlatform);
        $admitadResponse = $this->getData(
            $admitadPlatform->getBaseUrl().'advcampaigns/'.$cashBack->getExternalId().'/website/'.$admitadPlatform->getExternalPlatformId().'/', $admitadPlatform->getToken()
        );

        if (empty($admitadResponse)) {
            throw new \Exception('Нет ответа от admitad');
        }

        if (empty($admitadResponse['error'])) {
            $admitadResponse['update_result'] = $this->updateCashBack($cashBack, $admitadResponse);
        } else {
            $admitadResponse['update_result'] = $this->proceedError($cashBack, $admitadResponse);
        }

        $admitadResponse['raw_description'] = ''; // внутри косячная верстка, которая ломает js

        return $admitadResponse;
    }

    /**
     * Возвращает из адмитада список подключенных с нашей стороны сайтов.
     *
     * @return array|null
     */
    public function getWebSites(): ?array
    {
        $admitadPlatform = $this->getAdmitadPlatform();
        $this->updateAccessToken($admitadPlatform);

        return $this->getData($admitadPlatform->getBaseUrl().'websites/', $admitadPlatform->getToken());
    }

    /**
     * Возвращает список выплат по кешбекам
     *
     * @param CashBackPlatform $admitadPlatform
     * @param int              $offset
     * @param int              $limit
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getCashBackPayments(cashBackPlatform $admitadPlatform, int $offset, int $limit): array
    {
        $this->updateAccessToken($admitadPlatform);

        $url =
            $admitadPlatform->getBaseUrl().'statistics/sub_ids/?offset='.$offset.
            '&limit='.$limit.
            '&date_start='.$this->getStartDate().
            '&date_end='.$this->getEndDate();

        $admitadResponse = $this->getData($url, $admitadPlatform->getToken());

        if (empty($admitadResponse)) {
            throw new \Exception('Нет ответа от admitad');
        }

        return $admitadResponse;
    }

    /**
     * Запрос на партнерку с магазином в Admitad.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param CashBack         $cashBack
     *
     * @throws \Exception
     *
     * @return array
     */
    public function requirePartnership(CashBackPlatform $admitadPlatform, CashBack $cashBack): array
    {
        $this->updateAccessToken($admitadPlatform);

        //Сначала проверяем, может заявка уже подана
        $admitadResponse = $this->checkCampaign($admitadPlatform, $cashBack);

        if (!empty($admitadResponse['connection_status'])) {
            return $admitadResponse; //Если уже есть ответ - не нужно повторного запроса
        }

        //Заявка еще не подана - отправляем
        $url = $admitadPlatform->getBaseUrl().'advcampaigns/'.$cashBack->getExternalId().'/attach/'.$admitadPlatform->getExternalPlatformId().'/';
        $admitadResponse = $this->getData($url, $admitadPlatform->getToken(), self::POST_METHOD);

        if (empty($admitadResponse)) {
            throw new \Exception('Нет ответа от admitad');
        }

        $admitadResponse = $this->checkCampaign($admitadPlatform, $cashBack);

        return $admitadResponse;
    }

    /**
     * Получает компании из адмитада с offset, limit.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param int              $offset
     * @param int              $limit
     *
     * @return array
     */
    public function getCampaigns(CashBackPlatform $admitadPlatform, int $offset, int $limit): array
    {
        $url = $admitadPlatform->getBaseUrl().'advcampaigns/?offset='.$offset.'&limit='.$limit.'&has_tool=deeplink';

        return $this->getData($url, $admitadPlatform->getToken());
    }

    /**
     * Геттер инстанса платформы адмитада.
     *
     * @return CashBackPlatform
     */
    public function getAdmitadPlatform(): CashBackPlatform
    {
        //TODO убрать менеджер, передавать cashbackplatform через factory
        return $this->manager->getRepository(CashBackPlatform::class)->find(CashBackPlatform::ADMITAD_PLATFORM_ID);
    }

    /**
     * Создание нового кешбека по данным с адмитада.
     *
     * @param CashBackPlatform $admitadPlatform
     * @param array            $item
     * @param bool             $flushFlag
     *
     * @return CashBack
     */
    public function createCashBack(CashBackPlatform $admitadPlatform, array $item, bool $flushFlag = false): ?CashBack
    {
        $cashBackImage = $this->createCashBackImage($item);

        $condition = html_entity_decode(strip_tags($item['description']), ENT_HTML5).'|'.html_entity_decode(strip_tags($item['more_rules']), ENT_HTML5);
        $slugify = new Slugify();

        $cashBack = new CashBack();
        $cashBack->setActive(false);
        $cashBack->setExternalId($item['id']);
        $cashBack->setRating((int) $item['rating']);
        $cashBack->setCash('');
        $cashBack->setTitle($item['name']);
        $cashBack->setSlug($slugify->slugify($item['name']));
        $cashBack->setCondition($condition);
        $cashBack->setCashBackPlatform($admitadPlatform);
        $cashBack->setCashBackImage($cashBackImage);

        if ($item['avg_money_transfer_time'] > 0) {
            $cashBack->setAwaitingTime((int) $item['avg_money_transfer_time']);
        }

        if ($item['connected']) {
            $cashBack
                ->setStatus(CashBackStatusEnumType::APPROVED_PARTNERSHIP);
            //TODO set Uri for create query
        }

//        if (!empty($item['categories'])) { //TODO поиск уже существующих категорий
//            foreach ($item['categories'] as $action) {
//                $cashBackCategory = new CashBackCategory();
//                $cashBackCategory
        ////                    ->setExternalId($action['id'])
//                    ->setTitle($action['name'])
        ////                    ->setCash($action['payment_size'])
//                    ->setCashBack($cashBack);
//                $this->manager->persist($cashBackCategory);
//            }
//        }

        $this->manager->persist($cashBack);
        if ($flushFlag) {
            $this->manager->flush();
        }

        return $cashBack;
    }

    /**
     * Универсальный метод для запроса данных из Admitad.
     *
     * @param string $url
     * @param string $token
     * @param string $method
     *
     * @return array|null
     */
    protected function getData(string $url, string $token, $method = self::GET_METHOD): ?array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (self::POST_METHOD === $method) {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
        $data = curl_exec($ch);
        curl_close($ch);

        return json_decode($data, true);
    }

    /**
     * Обновляет поля уже существующего кешбека.
     *
     * @param CashBack $cashBack
     * @param array    $admitadResponse
     *
     * @return bool
     */
    protected function updateCashBack(CashBack $cashBack, array $admitadResponse): bool
    {
        $updateFlag = false;

        if ($cashBack->getExternalId() !== $admitadResponse['id']) {
            $cashBack->setExternalId($admitadResponse['id']);
            $updateFlag = true;
        }

        $rating = (float) $admitadResponse['rating'];
        if ($cashBack->getRating() !== $rating) {
            $cashBack->setRating($rating);
            $updateFlag = true;
        }

        $status = $this->getStatus($admitadResponse);
        if ($cashBack->getStatus() !== $status) {
            $cashBack->setStatus($status);
            $updateFlag = true;
        }

        if ($cashBack->getUrl() !== $admitadResponse['gotolink']) {
            $cashBack->setUrl($admitadResponse['gotolink']);
            $updateFlag = true;
        }

        if ($cashBack->getSiteUrl() !== $admitadResponse['site_url']) {
            $cashBack->setSiteUrl($admitadResponse['site_url']);
            $updateFlag = true;
        }

//        /* @var CashBackCategory $category */ //TODO обновление категорий
//        foreach ($admitadResponse['actions'] as $admitadAction) {
//            $founded = false;
//            foreach ($cashBack->getCategories() as $category) {
//
//                if (null !== $category->getExternalId()) {
//                    if ($category->getExternalId() === $admitadAction['id']) {
//                        $founded = true;
//                        if ($category->getTitle() !== $admitadAction['name'] || $category->getCash() !== $admitadAction['payment_size']) {
//                            $updateFlag = true;
//                            $category
//                                ->setTitle($admitadAction['name'])
//                                ->setCash($admitadAction['payment_size']);
//
//                            $this->manager->persist($category);
//                        }
//                    }
//                } else {
//                    if ($category->getTitle() === $admitadAction['name'] && $category->getCash() === $admitadAction['payment_size']) {
//                        $founded = true;
//                        $updateFlag = true;
//                        $category->setExternalId($admitadAction['id']);
//
//                        $this->manager->persist($category);
//                    }
//                }
//            }
//
//            if (!$founded) {
//                $updateFlag = true;
//                $category = new CashBackCategory();
//                $category
//                    ->setCashBack($cashBack)
//                    ->setExternalId($admitadAction['id'])
//                    ->setTitle($admitadAction['name'])
//                    ->setCash($admitadAction['payment_size']);
//
//                $this->manager->persist($category);
//            }
//        }

        //TODO проверка совпадения картинки
        if ($updateFlag) {
            $this->manager->persist($cashBack);
            $this->manager->flush();
        }

        return $updateFlag;
    }

    /**
     * Проверка ID платформы, этот хендлер работает только с ADMITAD.
     *
     * @param CashBackPlatform $admitadCashBackPlatform
     *
     * @throws \Exception
     */
    protected function checkPlatformId(CashBackPlatform $admitadCashBackPlatform): void
    {
        if (CashBackPlatform::ADMITAD_PLATFORM_ID !== $admitadCashBackPlatform->getId()) {
            throw new \Exception(self::WRONG_PLATFORM_ID);
        }
    }

    /**
     * Создаем изображение кешбека.
     *
     * @param array $item
     *
     * @return CashBackImage|null
     */
    protected function createCashBackImage(array $item): ?CashBackImage
    {
        file_put_contents(self::TMP_FILE, fopen($item['image'], 'rb'));

        $cashBackImage = new CashBackImage();
        $cashBackImage->setFile(new File(self::TMP_FILE));
        $this->manager->persist($cashBackImage);
        $this->manager->flush();

        return $cashBackImage;
    }

    /**
     * Обработка ошибки от адмитада.
     *
     * @param CashBack $cashBack
     * @param array    $admitadResponse
     *
     * @return bool
     */
    protected function proceedError(CashBack $cashBack, array $admitadResponse): bool
    {
        if (self::ADMITAD_MESSAGE_NOT_FOUND === $admitadResponse['error']) {
            $cashBack->setActive(false);

            $this->manager->persist($cashBack);
            $this->manager->flush();

            return true;
        }

        $this->logger->critical('Do not now how proceed error - '.$cashBack->getId());

        return false;
    }

    /**
     * Возвращает строковое представление самой ранней даты для поиска выплат
     *
     * @return string
     */
    private function getStartDate(): string
    {
        if (null === $this->startDate) {
            $this->startDate = (new \DateTime(self::OLDEST_PAYMENT_CHECK))->format(self::DATE_FORMAT);
        }

        return $this->startDate;
    }

    /**
     * Возвращает строковое представление самой поздней даты для поиска выплат
     *
     * @return string
     */
    private function getEndDate(): string
    {
        if (null === $this->endDate) {
            $this->endDate = (new \DateTime(self::NEWEST_PAYMENT_CHECK))->format(self::DATE_FORMAT);
        }

        return $this->endDate;
    }
}
