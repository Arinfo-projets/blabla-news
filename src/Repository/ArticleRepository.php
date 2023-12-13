<?php

namespace App\Repository;

use App\Data\SearchDataArticle;
use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    //    /**
    //     * @return Article[] Returns an array of Article objects
    //     */
    public function findSearch(SearchDataArticle $search): array
    {
        $query = $this
            ->createQueryBuilder('a');

            if(!empty($search->category)){
                $query = $query->andWhere('a.category = :category')
                ->setParameter('category', $search->category )
                ;
            }

        if (!empty($search->q)) {
            $query = $query
                ->andWhere('a.title LIKE :q OR a.content LIKE :q')
                ->setParameter('q', "%{$search->q}%");
        }


        return $query->getQuery()->getResult();
    }


    //    public function findOneBySomeField($value): ?Article
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
