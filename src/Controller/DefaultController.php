<?php

namespace App\Controller;

use App\Repository\CashBackRepository;
use App\Service\Api\AdmitadApiClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="main")
     *
     * @param CashBackRepository $cashBackRepository
     *
     * @param AdmitadApiClient   $client
     * @return Response
     */
    public function index(CashBackRepository $cashBackRepository, AdmitadApiClient $client): Response
    {
        $client->getTags();

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
