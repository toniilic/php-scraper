<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
    * @Route("/home", name="home")
    */
    public function index()
    {
        $number = random_int(0, 100);

        return $this->render('home/index.html.twig', [
            'number' => $number,
        ]);
    }

    /**
     * @Route("/show", name="show")
     */
    public function show()
    {
        return $this->render('home/show.html.twig');
    }
}