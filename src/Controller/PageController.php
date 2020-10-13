<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    /**
     * @Route("/", name="page_home")
     */
    public function home(Request $request, PostRepository $repository, PaginatorInterface $paginator)
    {
        return $this->render('page/home.html.twig', [
            'pagination' => $paginator->paginate($repository->getPageQuery(), $request->query->getInt('page', 1), 3)
        ]);
    }
}