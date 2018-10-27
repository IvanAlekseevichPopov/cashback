<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        return $this->render('public/main.html.twig');
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faqAction()
    {
        return $this->render('public/faq.html.twig');
    }

    /**
     * @Route("/policy", name="policy")
     */
    public function policyAction()
    {
        return $this->render('public/policy.html.twig');
    }

    /**
     * @Route("/conditions", name="conditions")
     */
    public function conditionsAction()
    {
        return $this->render('public/conditions.html.twig');
    }
}
