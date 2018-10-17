<?php

declare(strict_types = 1);

namespace App\Controller\Admin;

use App\Entity\CashBack;
use App\Entity\CashBackPlatform;
use App\Service\AdmitadApiHandler;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class AdminCashBackController
 *
 * @Route("/admin")
 */
class AdminCashBackController extends Controller
{
    const MESSAGE_NOT_ENOUGH_PARAMETERS = 'Переданы не все параметры';
    const MESSAGE_NOT_FOUND = 'Не найдено в бд';
    const MESSAGE_INVALID_ANSWER = 'Некорректный ответ от кешбек сервиса';

    /** @var AdmitadApiHandler */
    private $admitadApiHandler;

    /**
     * AdminCashBackController constructor.
     *
     * @param AdmitadApiHandler $admitadApiHandler
     */
    public function __construct(AdmitadApiHandler $admitadApiHandler)
    {
        $this->admitadApiHandler = $admitadApiHandler;
    }

    /**
     * Updates information about not connected cashback
     *
     * @Route(
     *     "/cashback/check_status",
     *     name="admin_check_cashback_status",
     *     methods={"POST"}
     * )
     *
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return JsonResponse
     */
    public function cashBackCheckStatusAction(Request $request, EntityManagerInterface $manager)
    {
        //TODO refactoring: symfony forms, remove platform id
        $platformId = $request->get('platformId');
        $extId = $request->get('extId');

        if (empty($platformId) || empty($extId)) {
            return JsonResponse::create(self::MESSAGE_NOT_ENOUGH_PARAMETERS, Response::HTTP_BAD_REQUEST);
        }

        $cashBackPlatform = $manager->getRepository(CashBackPlatform::class)->find($platformId);
        if (empty($cashBackPlatform)) {
            return JsonResponse::create(self::MESSAGE_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $cashBack = $manager->getRepository(CashBack::class)->findOneBy(['cashBackPlatform' => $cashBackPlatform, 'externalId' => $extId]);
        if (empty($cashBack)) {
            return JsonResponse::create(self::MESSAGE_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        $res = $this->checkDataFromCashBackService($cashBackPlatform, $cashBack);

        if (empty($res)) {
            return JsonResponse::create(self::MESSAGE_INVALID_ANSWER, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return JsonResponse::create($res);
    }

//    /**
//     * Обновляет информацию об акции с кешбек-сервиса
//     *
//     * @Method("POST")
//     * @Route(
//     *     "/cashback/send_cooperation_offer",
//     *     name="sonata_admin_custom_send_cooperation_offer",
//     *     options={
//     *          "expose": true
//     *     }
//     * )
//     *
//     * @param Request $request
//     *
//     * @return JsonResponse
//     */
//    public function cashBackSendPartnerShipAction(Request $request): JsonResponse
//    {
//        $platformId = $request->get('platformId');
//        $extId = $request->get('extId');
//
//        if (empty($platformId) || empty($extId)) {
//            return $this->returnErrorJson(self::MESSAGE_NOT_ENOUGH_PARAMETERS, ApiControllerAbstract::RESPONSE_CODE_BAD_REQUEST);
//        }
//
//        $cashBackPlatform = $this->get('doctrine.orm.default_entity_manager')->getRepository(CashBackPlatform::class)->find($platformId);
//        if (empty($cashBackPlatform)) {
//            return $this->returnErrorJson(self::MESSAGE_NOT_FOUND, ApiControllerAbstract::RESPONSE_CODE_NOT_FOUND);
//        }
//
//        $cashBack = $this->get('doctrine.orm.default_entity_manager')->getRepository(CashBack::class)->findOneBy(['cashBackPlatform' => $cashBackPlatform, 'externalId' => $extId]);
//        if (empty($cashBack)) {
//            return $this->returnErrorJson(self::MESSAGE_NOT_FOUND, ApiControllerAbstract::RESPONSE_CODE_NOT_FOUND);
//        }
//        $res = $this->requirePartnership($cashBackPlatform, $cashBack);
//
//        if (empty($res)) {
//            return $this->returnErrorJson(self::MESSAGE_INVALID_ANSWER, ApiControllerAbstract::RESPONSE_CODE_INTERNAL_SERVER_ERROR);
//        }
//
//        return $this->returnSuccessJson($res);
//    }
//
    /**
     * Отправка запроса в кешбек сервис для обновления данных кампании
     *
     * @param CashBackPlatform $cashBackPlatform
     * @param CashBack         $cashBack
     *
     * @return array
     */
    protected function checkDataFromCashBackService(CashBackPlatform $cashBackPlatform, CashBack $cashBack): array
    {
        switch ($cashBackPlatform->getId()) {
            case CashBackPlatform::ADMITAD_PLATFORM_ID:
                return $this->admitadApiHandler->checkCampaign($cashBackPlatform, $cashBack);
            default:
                throw new \LogicException('CashBack Platform unknown. Logic for it was not written');
        }
    }
//
//    /**
//     * Отправка запроса на партнеку
//     *
//     * @param CashBackPlatform $cashBackPlatform
//     * @param CashBack         $cashBack
//     *
//     * @return array
//     */
//    protected function requirePartnership(CashBackPlatform $cashBackPlatform, CashBack $cashBack): array
//    {
//        switch ($cashBackPlatform->getId()) {
//            case CashBackPlatform::ADMITAD_PLATFORM_ID:
//                return $this->getAdmitadApiHandler()->requirePartnership($cashBackPlatform, $cashBack);
//            default:
//                throw new UnknownCashBackPlatformException('CashBack Platform unknown. Logic for it was not written');
//        }
//    }
}
