<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Util\ImageMagician;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, Serializable
{
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_MODER = 'ROLE_MODER';
    public const ROLE_AUTHOR = 'ROLE_AUTHOR';
    public const ROLE_USER = 'ROLE_USER';

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string
     */
    private $repeatPassword;

    /**
     * @var ArrayCollection|Post[]
     *
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="author", orphanRemoval=true)
     */
    private $posts;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @var UploadedFile
     */
    private $avatarFile;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenLifetime;

    /**
     * @ORM\ManyToMany(targetEntity=Post::class)
     * @ORM\JoinTable(name="user_saved_posts",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id")}
     *      )
     */
    private $savedPosts;

    /**
     * @ORM\ManyToMany(targetEntity=User::class)
     * @ORM\JoinTable(name="user_subscribed_authors",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="author_id", referencedColumnName="id")}
     *      )
     */
    private $subscribedAuthors;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->savedPosts = new ArrayCollection();
        $this->subscribedAuthors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getRepeatPassword(): ?string
    {
        return $this->repeatPassword;
    }

    /**
     * @param string $repeatPassword
     */
    public function setRepeatPassword(?string $repeatPassword): void
    {
        $this->repeatPassword = $repeatPassword;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $ye): self
    {
        if (!$this->posts->contains($ye)) {
            $this->posts[] = $ye;
            $ye->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $ye): self
    {
        if ($this->posts->contains($ye)) {
            $this->posts->removeElement($ye);
            // set the owning side to null (unless already changed)
            if ($ye->getAuthor() === $this) {
                $ye->setAuthor(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenLifetime(): ?DateTimeInterface
    {
        return $this->tokenLifetime;
    }

    public function setTokenLifetime(?DateTimeInterface $tokenLifetime): self
    {
        $this->tokenLifetime = $tokenLifetime;

        return $this;
    }

    /**
     * @return UploadedFile|null
     */
    public function getAvatarFile(): ?UploadedFile
    {
        return $this->avatarFile;
    }

    /**
     * @param UploadedFile|null $avatarFile
     */
    public function setAvatarFile(?UploadedFile $avatarFile): void
    {
        $this->avatarFile = $avatarFile;
    }

    public function saveAvatar()
    {
        $newFilename = $this->id . '_' . md5($this->username) . ".jpeg";
        $this->avatarFile->move(__DIR__ . '/../../public/avatars', $newFilename);
        $this->avatar = 'avatars/' . $newFilename;

        $fullPath = __DIR__ . '/../../public/avatars/' . $newFilename;
        $avatar = new ImageMagician($fullPath);
        $avatar->resizeImage(150, 150, 'crop');
        $avatar->saveImage($fullPath, 80);
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->email,
            $this->roles,
            $this->password,
            $this->avatar,
            $this->token,
            $this->tokenLifetime,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->roles,
            $this->password,
            $this->avatar,
            $this->token,
            $this->tokenLifetime,
            ) = unserialize($serialized);
    }

    /**
     * @return Collection|Post[]
     */
    public function getSavedPosts(): Collection
    {
        return $this->savedPosts;
    }

    public function addSavedPost(Post $savedPost): self
    {
        if (!$this->savedPosts->contains($savedPost)) {
            $this->savedPosts[] = $savedPost;
        }

        return $this;
    }

    public function removeSavedPost(Post $savedPost): self
    {
        if ($this->savedPosts->contains($savedPost)) {
            $this->savedPosts->removeElement($savedPost);
        }

        return $this;
    }

    public function isSavedPost(Post $savedPost): bool
    {
        return $this->savedPosts->contains($savedPost);
    }

    /**
     * @return Collection|User[]
     */
    public function getSubscribedAuthors(): Collection
    {
        return $this->subscribedAuthors;
    }

    public function addSubscribedAuthors(User $subscribedAuthor): self
    {
        if (!$this->subscribedAuthors->contains($subscribedAuthor)) {
            $this->subscribedAuthors[] = $subscribedAuthor;
        }

        return $this;
    }

    public function removeSubscribedAuthor(User $subscribedAuthor): self
    {
        if ($this->subscribedAuthors->contains($subscribedAuthor)) {
            $this->subscribedAuthors->removeElement($subscribedAuthor);
        }

        return $this;
    }

    public function isSubscribedAuthor(User $subscribedAuthor): bool
    {
        return $this->subscribedAuthors->contains($subscribedAuthor);
    }
}
