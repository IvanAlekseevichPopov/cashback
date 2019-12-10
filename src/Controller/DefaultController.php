<?php

namespace App\Controller;

use App\Repository\CashBackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="main")
     *
     * @param CashBackRepository $cashBackRepository
     *
     * @return Response
     */
    public function index(CashBackRepository $cashBackRepository): Response
    {
        $cashbacks = $cashBackRepository->getCashbackTop();

        return $this->render('public/main.html.twig', [
                'cashbacks' => $cashbacks,
            ]
        );
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faqAction(): Response
    {
        return $this->render('public/faq.html.twig');
    }

    /**
     * @Route("/policy", name="policy")
     */
    public function policyAction(): Response
    {
        return $this->render('public/policy.html.twig');
    }

    /**
     * @Route("/conditions", name="conditions")
     */
    public function conditionsAction(): Response
    {
        return $this->render('public/conditions.html.twig');
    }
}
