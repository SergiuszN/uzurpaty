<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllAwaiting()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_AWAIT)
            ->orderBy('p.created', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getPageQuery(Request $request)
    {
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_POSTED)
            ->orderBy('p.created', 'DESC');

        if ($request->query->get('search', null)) {
            $query
                ->andWhere('p.content LIKE :search')
                ->orWhere('p.name LIKE :search')
                ->setParameter('search', '%' . $request->query->get('search', null) . '%');
        }

        if ($request->query->get('user', null)) {
            $query
                ->andWhere('p.author = :author')
                ->setParameter('author', $request->query->get('user', null));
        }

        if ($request->query->get('category', null)) {
            $query
                ->leftJoin('p.category', 'c')
                ->andWhere('c.id = :category')
                ->setParameter('category', $request->query->get('category', null));
        }

        if ($request->query->get('country', null)) {
            $query
                ->andWhere('p.country = :country')
                ->setParameter('country', $request->query->get('country', null));
        }

        return $query->getQuery();
    }

    /**
     * @return array
     */
    public function findAllUniqCountries($locale = 'ru')
    {
        $posts = $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', Post::STATUS_POSTED)
            ->groupBy('p.country')
            ->getQuery()
            ->getResult();

        return array_map(function (Post $post) use($locale) {
            return [
                'code' => $post->getCountry(),
                'name' => $post->getCountryName($locale),
            ];
        }, $posts);
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
