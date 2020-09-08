<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="page_home")
     */
    public function home()
    {
        return new Response('<html lang="ru"><head><title>Вахахах</title></head><body><h1>Привет мир</h1></body></html>');
    }
}