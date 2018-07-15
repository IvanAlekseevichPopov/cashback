<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * StaticController
 *
 * @author Popov Ivan <ivan.alekseevich.popov@gmail.com>
 */
class StaticController extends Controller
{
    /**
     * @Route("/faq", name="faq")
     */
    public function index()
    {
        return $this->render('public/faq.html.twig', [
            'tst' => 'ads',
        ]);
    }
}
