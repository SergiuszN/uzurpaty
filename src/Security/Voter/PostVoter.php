<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    const SHOW = 'SHOW';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::SHOW])) {
            return false;
        }

        if (!$subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        switch ($attribute) {
            case self::SHOW:
                return $this->canShow($subject, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    /**
     * @param Post $post
     * @param UserInterface|User|null $user
     * @return bool
     */
    private function canShow(Post $post, $user)
    {
        if ($post->getStatus() !== Post::STATUS_POSTED) {
            if (!($user instanceof UserInterface)) {
                return false;
            } else {
                if (($post->getAuthor()->getId() !== $user->getId()) || (!$this->security->isGranted('ROLE_MODER')))
                    return false;
            }
        }

        return true;
    }
}