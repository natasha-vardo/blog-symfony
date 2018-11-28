<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

     /**
    / * @return Post[] Returns an array of Post objects
      */

    public function findByDate()
    {
        return $this->createQueryBuilder('post')
            ->join('post.author', 'username')
            ->where('post.isActive = :active')
            ->setParameter('active', 1)
            ->orderBy('post.created', 'DESC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByLikes()
    {
        return $this->createQueryBuilder('post')
            ->join('post.author', 'username')
            ->orderBy('post.likes', 'DESC')
            ->where('post.isActive = :active')
            ->setParameter('active', 1)
            ->andWhere('post.created > :date')
            ->setParameter('date', date('Y-m-d H:i:s', time() - 86400 * 7))
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMyPosts($username)
    {
        return $this->createQueryBuilder('post')
            ->join('post.author', 'user')
            ->where('user.username = :author')
            ->setParameter('author', $username)
            ->orderBy('post.created', 'DESC')
            ->getQuery()
            ->getResult()
            ;

    }

    public function findBloggerPosts($username)
    {
        return $this->createQueryBuilder('post')
            ->join('post.author', 'user')
            ->where('user.username = :author')
            ->andWhere('post.isActive = :active')
            ->setParameter('author', $username)
            ->setParameter('active', 1)
            ->orderBy('post.created', 'DESC')
            ->getQuery()
            ->getResult()
            ;

    }
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
