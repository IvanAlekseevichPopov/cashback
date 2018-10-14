<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\CashBack;
use App\Entity\CashBackTrek;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
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
        ]);
    }

    /**
     * @Route("/catalog", name="catalog", methods={"GET"})
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

    /**
     * @Route("/cashback/{id}", name="cashback_tracking")
     * @Entity(name="cashback", expr="repository.findByUuid(id)")
     *
     * @param CashBack               $cashback
     * @param EntityManagerInterface $entityManager
     */
    public function createCashbackTracking(Cashback $cashback, EntityManagerInterface $entityManager)
    {
        if(null === $this->getUser()){
            //TODO ставим кэшбек, ведем статистику
        }

        dump($this->genUrl($this->getUser(), $cashback, $entityManager));
//        $shopUrl =
//        dump($cashback);


    }

    /**
     * @param User                   $user
     * @param CashBack               $cashBack
     * @param EntityManagerInterface $manager
     *
     * @return string
     */
    private function genUrl(User $user, CashBack $cashBack, EntityManagerInterface $manager): string
    {
        $cashBackTrek = new CashBackTrek($user, $cashBack);

        $manager->persist($cashBackTrek);
        $manager->flush();

        return sprintf('%s?subid=%s', $cashBack->getUrl(), $cashBackTrek->getId());
    }
}
