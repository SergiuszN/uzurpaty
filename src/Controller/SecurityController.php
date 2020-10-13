<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
use App\Service\EmailSender;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/security/login", name="security_login")
     */
    public function login()
    {
        return $this->redirectToRoute('page_home');
    }

    /**
     * @Route("/security/logout", name="security_logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/security/forgot", name="security_forgot")
     * @throws Exception
     */
    public function forgot(Request $request, EntityManagerInterface $em, EmailSender $mailer)
    {
        $form = $this->createForm(ForgotPasswordType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = $em->getRepository(User::class);
            $user = $repository->findOneByEmail($form->getData()->getEmail());

            if ($user) {
                $user->setToken(bin2hex(random_bytes(10)) . time());
                $user->setTokenLifetime((new DateTime())->modify('+1 hour'));
                $em->flush();

                $mailer->forgotPassword($user);
            }

            return $this->redirectToRoute('page_home');
        }

        return $this->render('security/forgot.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/security/set-new-password/{token}", name="security_set_new_password")
     */
    public function setNewPassword($token, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(NewPasswordType::class)->handleRequest($request);
        $user = $em->getRepository(User::class)->findOneByToken($token);

        if (!$user) {
            return $this->redirectToRoute('page_home');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $form->getData()->getPassword()));
            $user->setToken(null);
            $user->setTokenLifetime(null);
            $em->flush();

            return $this->redirectToRoute('page_home');
        }

        return $this->render('security/set_new_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/security/register", name="security_register")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createForm(RegisterType::class)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $user->setPassword($encoder->encodePassword($user, $form->getData()->getPassword()));
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('page_home');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
