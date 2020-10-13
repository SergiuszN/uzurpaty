<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailSender
{
    /** @var MailerInterface */
    private $mailer;

    /** @var ParameterBagInterface */
    private $bag;

    /** @var UrlGeneratorInterface */
    private $generator;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $bag, UrlGeneratorInterface $generator)
    {
        $this->mailer = $mailer;
        $this->bag = $bag;
        $this->generator = $generator;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function send($to, $subject, $html)
    {
        $email = (new Email())
            ->from($this->bag->get('mailer.from.email'))
            ->to($to)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }

    /**
     * @param UserInterface|User $user
     */
    public function forgotPassword(UserInterface $user)
    {
        $url = $this->generator->generate('security_set_new_password', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->send($user->getEmail(), 'Вы запросили сброс пароля!', trim("
            Что бы сбросить пароль просто перейдите по ссылке: <a href=\"{$url}\">$url</a> <br>
            Если вы не запрашивали смену пароля просто проигнорируйте это сообщение.
            "));
    }
}