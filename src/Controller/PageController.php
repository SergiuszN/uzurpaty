<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="page_home")
     */
    public function home(PostRepository $repository)
    {
        return $this->render('page/home.html.twig', [
            'posts' => $repository->findAll()
        ]);
    }
}