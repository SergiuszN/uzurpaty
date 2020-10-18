<?php

namespace App\Service\Widget;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class SearchWidget
{
    /** @var Post|null */
    private $post;

    /** @var object */
    private $filters;

    /** @var RouterInterface */
    private $router;

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $route;

    public function __construct(RequestStack $requestStack, RouterInterface $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em = $em;

        $request = $requestStack->getCurrentRequest();

        $this->filters = (object) [
            'author' => $request ? $request->query->get('author', null) : null,
            'category' => $request ? $request->query->get('category', null) : null,
            'country' => $request ? $request->query->get('country', null) : null,
            'search' => $request ? $request->query->get('search', null) : null,
        ];

        $this->route = $request->get('_route');
        $this->post = null;
    }

    public function findAllCategories()
    {
        return $this->em->getRepository(Category::class)->findAllActive();
    }

    public function findAllCountries()
    {
        return $this->em->getRepository(Post::class)->findAllUniqCountries();
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
    }

    public function isSearchPanel()
    {
        return $this->post && ($this->filters->author || $this->filters->category || $this->filters->country || $this->filters->search);
    }

    public function getAuthor()
    {
        return $this->filters->author;
    }

    public function getAuthorName()
    {
        return $this->post->getAuthor()->getUsername();
    }

    public function getCategory()
    {
        return $this->filters->category;
    }

    public function getCategoryName()
    {
        return $this->post->getCategory()->getName();
    }

    public function getCountry()
    {
        return $this->filters->country;
    }

    public function getCountryName()
    {
        return $this->post->getCountryName();
    }

    public function getSearch()
    {
        return $this->filters->search;
    }

    public function generateRoute($type, $value)
    {
        $params = (array)$this->filters;
        $params[$type] = $value;
        return $this->router->generate($this->route, $params);
    }
}