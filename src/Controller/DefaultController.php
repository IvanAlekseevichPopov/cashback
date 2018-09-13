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
        //TODO передать пользователя если есть без субзапроса во все шаблоны

        return $this->render('public/main.html.twig');
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faqAction()
    {
        return $this->render('public/faq.html.twig');
    }
}
