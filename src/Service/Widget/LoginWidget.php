<?php

namespace App\Service\Widget;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginWidget
{
    /** @var AuthenticationException|null  */
    private $lastError;

    /** @var string */
    private $lastUsername;

    public function __construct(AuthenticationUtils $authenticationUtils, SessionInterface $session, TranslatorInterface $translator)
    {
        $this->lastError = $authenticationUtils->getLastAuthenticationError();
        $this->lastUsername = $authenticationUtils->getLastUsername();

        if ($this->lastError) {
            $session->getFlashBag()->set('login-error', $translator->trans($this->lastError->getMessageKey(), $this->lastError->getMessageData(), 'security'));
        }
    }

    public function getLastUsername()
    {
        return $this->lastUsername;
    }
}