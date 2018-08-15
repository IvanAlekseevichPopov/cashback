<?php

declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    /**
     * @Route("/cabinet", name="cabinet")
     */
    public function index()
    {
        return $this->render('public/cabinet.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction()
    {
        //TODO realise
//        return $this->render('public/cabinet.html.twig');
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function registerAction()
    {
//        return $this->render('public/cabinet.html.twig');
    }
}
