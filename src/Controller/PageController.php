<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Form\AddCommentType;
use App\Form\EditProfileType;
use App\Repository\PostRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/saved", name="page_saved")
     */
    public function saved(Request $request, PostRepository $repository, PaginatorInterface $paginator)
    {
        return $this->render('page/home.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->getSavedPageQuery($this->getUser(), $request),
                $request->query->getInt('page', self::FIRST_PAGE),
                self::POST_PER_PAGE
            )
        ]);
    }

    /**
     * @Route("/subscribed", name="page_subscribed")
     */
    public function subscribed(Request $request, PostRepository $repository, PaginatorInterface $paginator)
    {
        return $this->render('page/home.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->getSubscribedAuthorsPageQuery($this->getUser(), $request),
                $request->query->getInt('page', self::FIRST_PAGE),
                self::POST_PER_PAGE
            )
        ]);
    }

    /**
     * @Route("/post/{post}", name="page_post")
     * @IsGranted("SHOW", subject="post")
     */
    public function post(Post $post, EntityManagerInterface $em, Request $request)
    {
        $post->increaseOpened();
        $em->flush();

        $formAddComment = $this->createForm(AddCommentType::class)
            ->handleRequest($request);

        if ($formAddComment->isSubmitted() && $formAddComment->isValid()) {
            /** @var Comment $comment */
            $comment = $formAddComment->getData();
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $comment->setVisible(true);
            $comment->setCreated(new DateTime());
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('page_post', ['post' => $post->getId()]);
        }

        return $this->render('page/post.html.twig', [
            'form_add_comment' => $formAddComment->createView(),
            'post' => $post,
        ]);
    }

    /**
     * @Route("/profile", name="page_profile")
     * @IsGranted("ROLE_USER")
     */
    public function profile(Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(EditProfileType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getAvatarFile()) {
                $user->saveAvatar();
            }

            $em->flush();
        }

        return $this->render('page/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/save-post/{post}", name="page_save_post")
     * @IsGranted("ROLE_USER")
     */
    public function savePost(Post $post, Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isSavedPost($post)) {
            $user->removeSavedPost($post);
        } else {
            $user->addSavedPost($post);
        }

        $em->flush();

        $referrer = $request->headers->get('referer', '');

        return !$referrer || strpos($referrer, 'save-post') !== false
            ? $this->redirectToRoute('page_home')
            : $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/subscribe-author/{author}", name="page_subscribe_author")
     * @IsGranted("ROLE_USER")
     */
    public function subscribeAuthor(User $author, Request $request, EntityManagerInterface $em)
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isSubscribedAuthor($author)) {
            $user->removeSubscribedAuthor($author);
        } else {
            $user->addSubscribedAuthors($author);
        }

        $em->flush();

        $referrer = $request->headers->get('referer', '');

        return !$referrer || strpos($referrer, 'subscribe-author') !== false
            ? $this->redirectToRoute('page_home')
            : $this->redirect($request->headers->get('referer'));
    }
}