<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\AdminPostCreateType;
use App\Form\AdminPostEditType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/post/list", name="admin_post_list")
     * @IsGranted("ROLE_ADMIN_POST_LIST")
     */
    public function postList(PostRepository $repository)
    {
        return $this->render('admin/post/list.html.twig', [
            'posts' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/admin/post/add", name="admin_post_add")
     * @IsGranted("ROLE_ADMIN_POST_ADD")
     */
    public function postAdd(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(AdminPostCreateType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Post $post */
            $post = $form->getData();
            $post->setAuthor($this->getUser());
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'Пост успешно создан');
            return $this->redirectToRoute('admin_post_list');
        }

        return $this->render('admin/post/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/post/remove/{post}", name="admin_post_remove")
     * @IsGranted("ROLE_ADMIN_POST_REMOVE")
     */
    public function postRemove(Post $post, EntityManagerInterface $em)
    {
        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'Пост успешно удален');
        return $this->redirectToRoute('admin_post_list');
    }

    /**
     * @Route("/admin/post/edit/{post}", name="admin_post_edit")
     * @IsGranted("ROLE_ADMIN_POST_EDIT")
     */
    public function postEdit(Post $post, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(AdminPostEditType::class, $post)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Пост успешно отредактирован');
            return $this->redirectToRoute('admin_post_list');
        }

        return $this->render('admin/post/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/post/comment/list/{post}", name="admin_post_comment_list")
     * @IsGranted("ROLE_ADMIN_POST_COMMENT_LIST")
     */
    public function postCommentList(Post $post)
    {
        return $this->render('admin/post/comments.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/admin/post/awaiting/{post}", name="admin_post_awaiting_list")
     * @IsGranted("ROLE_ADMIN_POST_AWAITING_LIST")
     */
    public function postAwaitingList(PostRepository $repository)
    {
        return $this->render('admin/post/awaitingList.html.twig', [
            'posts' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/admin/post/review/{post}", name="admin_post_review")
     * @IsGranted("ROLE_ADMIN_POST_REVIEW")
     */
    public function postReview(Post $post, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(AdminPostReviewType::class, $post)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Изменения успешно записанны');
            return $this->redirectToRoute('admin_post_awaiting_list');
        }

        return $this->render('admin/post/review.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/list", name="admin_category_list")
     * @IsGranted("ROLE_ADMIN_CATEGORY_LIST")
     */
    public function categoryList(CategoryRepository $repository)
    {
        return $this->render('admin/category/list.html.twig', [
            'categories' => $repository->findAll()
        ]);
    }

    /**
     * @Route("/admin/category/add", name="admin_category_add")
     * @IsGranted("ROLE_ADMIN_CATEGORY_ADD")
     */
    public function categoryAdd(Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(AdminCategoryCreateType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
            $this->addFlash('success', 'Категория успешно создана');
            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('admin/category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/remove/{category}", name="admin_category_remove")
     * @IsGranted("ROLE_ADMIN_CATEGORY_REMOVE")
     */
    public function categoryRemove(Category $category, EntityManagerInterface $em)
    {
        if ($category->getPosts()->count() > 0) {
            $this->addFlash('error', 'В этой категории есть посты. Она не может быть удалена.');
            return $this->redirectToRoute('admin_category_list');
        }

        $em->remove($category);
        $em->flush();
        $this->addFlash('success', 'Категория успешно удалена');
        return $this->redirectToRoute('admin_category_list');
    }

    /**
     * @Route("/admin/category/edit/{category}", name="admin_category_edit")
     * @IsGranted("ROLE_ADMIN_CATEGORY_EDIT")
     */
    public function categoryEdit(Category $category, Request $request, EntityManagerInterface $em)
    {
        $form = $this->createForm(AdminCategoryEditType::class, $category)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Категория успешно измененна.');
            return $this->redirectToRoute('admin_category_list');
        }

        return $this->render('admin/category/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/category/edit/{category}", name="admin_category_toggle")
     * @IsGranted("ROLE_ADMIN_CATEGORY_TOGGLE")
     */
    public function categoryToggle(Category $category, EntityManagerInterface $em)
    {
        $category->setActive(!$category->getActive());
        $em->flush();
        return $this->redirectToRoute('admin_category_list');
    }
}