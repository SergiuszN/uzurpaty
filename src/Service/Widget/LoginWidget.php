<?php

namespace App\Service\Widget;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginWidget
{
    /** @var AuthenticationException|null  */
    private $lastError;

    /** @var string */
    private $lastUsername;

    public function __construct(AuthenticationUtils $authenticationUtils, FlashBagInterface $bag, TranslatorInterface $translator)
    {
        $this->lastError = $authenticationUtils->getLastAuthenticationError();
        $this->lastUsername = $authenticationUtils->getLastUsername();

        if ($this->lastError) {
            $bag->set('login-error', $translator->trans($this->lastError->getMessageKey(), $this->lastError->getMessageData(), 'security'));
        }
    }

    public function getLastUsername()
    {
        return $this->lastUsername;
    }
}