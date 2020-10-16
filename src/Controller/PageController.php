<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PageController extends AbstractController
{
    const POST_PER_PAGE = 3;
    const FIRST_PAGE = 1;

    /**
     * @Route("/", name="page_home")
     */
    public function home(Request $request, PostRepository $repository, PaginatorInterface $paginator)
    {
        return $this->render('page/home.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->getPageQuery($request),
                $request->query->getInt('page', self::FIRST_PAGE),
                self::POST_PER_PAGE
            )
        ]);
    }

    /**
     * @Route("/post/{post}", name="page_post")
     */
    public function post(Post $post, EntityManagerInterface $em)
    {
        /** @var UserInterface|User $user */
        $user = $this->getUser();

        if ($post->getStatus() !== Post::STATUS_POSTED) {
            if (!$user) {
                throw $this->createNotFoundException();
            } else {
                if (($post->getAuthor()->getId() !== $user->getId()) || (!$this->isGranted('ROLE_MODER')))
                    throw $this->createNotFoundException();
            }
        }

        $post->increaseOpened();
        $em->flush();

        return $this->render('page/post.html.twig', [
            'post' => $post
        ]);
    }
}