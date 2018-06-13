<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\CashBack;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CashbackController
 */
class CashbackController extends Controller
{
    /**
     * @Route("/cashback", name="cashback")
     *
     * @Method("GET")
     */
    public function casbackListAction(Request $request, EntityManagerInterface $manager)
    {
        //TODO pagination
        $cashBackCollection = $manager->getRepository(CashBack::class)->findBy([], null, 20);
        //TODO только активные и подтвержденные

        return $this->render('public/cashback/list.html.twig', [
            'cashbacks' => $cashBackCollection,
        ]);
    }
}
