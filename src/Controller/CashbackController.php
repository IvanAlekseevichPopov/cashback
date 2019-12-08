<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CashBack;
use App\Form\Query\CashbackQueryType;
use App\Model\Query\CashbackQuery;
use App\Service\CashbackRedirectHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CashbackController extends AbstractController
{
    /**
     * @Route("/catalog/{slug}", name="cashback_page", methods={"GET"})
     *
     * @param string $slug
     *
     * @return Response
     */
    public function cashbackShowAction(string $slug)
    {
        $cashBack = $this->getDoctrine()->getRepository(CashBack::class)->getBySlug($slug, $this->getUser());
        if (null === $cashBack) {
            throw new NotFoundHttpException();
        }

        return $this->render('public/cashback/show.html.twig', [
            'cashback' => $cashBack,
            'comments' => [],
        ]);
    }

    /**
     * @Route("/catalog", name="catalog", methods={"GET"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function cashbackListAction(Request $request)
    {
        $cashbackQuery = new CashbackQuery();
        $form = $this->createForm(CashbackQueryType::class, $cashbackQuery);
        $form->submit($request->query->all());
        if (false === $form->isValid()) {
            throw new BadRequestHttpException('Bad request');
        }

        $cashBackCollection = $this->getDoctrine()->getRepository(CashBack::class)->getActiveCashBacks($cashbackQuery);

        return $this->render('public/cashback/list.html.twig', [
            'cashbacks' => $cashBackCollection,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cashback/{id}", name="cashback_tracking")
     * @Entity(name="cashback", expr="repository.findByUuid(id)")
     *
     * @param CashBack                $cashback
     * @param CashbackRedirectHandler $redirectHandler
     *
     * @return Response
     */
    public function createCashbackTracking(Cashback $cashback, CashbackRedirectHandler $redirectHandler)
    {
        if (null === $this->getUser()) {
            //TODO ставим кэшбек, ведем статистику
        }

        $url = $redirectHandler->generateRedirectUrl($cashback, $this->getUser());

        return new RedirectResponse($url);
    }
}
