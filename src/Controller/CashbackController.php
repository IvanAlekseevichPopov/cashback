<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CashBack;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CashbackController.
 */
class CashbackController extends Controller
{
    /**
     * @Route("/catalog/{slug}", name="cashback_page")
     * @Method("GET")
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
        ]);
    }

    /**
     * @Route("/catalog", name="catalog")
     * @Method("GET")
     *
     * @param EntityManagerInterface $manager
     *
     * @return Response
     */
    public function cashbackListAction(EntityManagerInterface $manager)
    {
        //TODO pagination
        $cashBackCollection = $manager->getRepository(CashBack::class)->findBy([], null, 20);

        //TODO только активные и подтвержденные

        return $this->render('public/cashback/list.html.twig', [
            'cashbacks' => $cashBackCollection,
        ]);
    }
}
