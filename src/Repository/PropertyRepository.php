<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Category;
use Doctrine\Common\Persistence\ManagerRegistry;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;

use App\Entity\Property;
use App\Entity\PropertySearch;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Property::class);
        $this->paginator = $paginator;
    }

    /**
     * @return Property[]
     */
    public function findAllNotSolded(): array
    {
        return $this->findNotSoldedQuery()
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $limit
     * @return Property[]
     */
    public function findLatests(int $limit = 4): array
    {
        return $this->findNotSoldedQuery()
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->innerJoin('p.category', 'category')
            ->addSelect('category')
            ->innerJoin('p.type', 'type')
            ->addSelect('type')
            ->innerJoin('p.proprietary', 'proprietary')
            ->addSelect('proprietary')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param PropertySearch $search
     * @param Category|null $category
     * @return QueryBuilder
     */
    public function findAllNotSoldedQuery(PropertySearch $search, Category $category = null): QueryBuilder
    {
        $query = $this->findNotSoldedQuery()
            ->innerJoin('p.type', 'type')
            ->addSelect('type')
            ->innerJoin('p.proprietary', 'proprietary')
            ->addSelect('proprietary')
            ->innerJoin('p.category', 'category')
            ->addSelect('category');

        if ($category) {
            $query = $query->andWhere('p.category = :category')
                ->setParameter('category', $category)
                ;
        }

        if ($search->getMaxPrice()) {
            $query = $query->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', $search->getMaxPrice());
        }

        if ($search->getMinSurface()) {
            $query = $query->andWhere('p.surface >= :minSurface')
                ->setParameter('minSurface', $search->getMinSurface());
        }

        if ($search->getTags()->count() > 0) {
            $k = 0;
            foreach ($search->getTags() as $tag) {
                $k++;
                $query = $query
                    ->andWhere(":tag$k MEMBER OF p.tags")
                    ->setParameter("tag$k", $tag);
            }
        }

        return $query;
    }
    /**
     * @return QueryBuilder
     */
    public function findNotSoldedQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->where('p.sold = false')
            ->where('p.isPublished = :published')
            ->setParameter('published', true)
            ;
    }
    /**
     * @return QueryBuilder
     */
    public function findAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p');
    }

    /**
     * Récupère les propriétés en lien avec un recherche
     * @param SearchData $searchData
     * @return PaginationInterface
     */
    public function findSearch(SearchData $searchData): PaginationInterface
    {

        $query = $this->getSearchQuery($searchData)->getQuery();

        return $this->paginator->paginate(
            $query,
            $searchData->page,
            15
        );
    }
    public function findMinMax(SearchData $searchData)
    {
        $results = $this->getSearchQuery($searchData)
            ->select('MIN(p.price) as min', 'MAX(p.price) as max')
            ->getQuery()
            ->getScalarResult();
        return [(int) $results[0]['min'], (int) $results[0]['max']];
    }

    private function getSearchQuery(SearchData $searchData, $ignorePrice = false): QueryBuilder
    {
        $query = $this
            ->findNotSoldedQuery()
            ->innerJoin('p.type', 'type')
            ->innerJoin('p.proprietary', 'proprietary')
            ->join('p.category', 'c')
            ->select('c', 'p', 'type', 'proprietary')
        ;

        if (!empty($searchData->q)) {
            $query = $query
                ->andWhere('p.name LIKE :q')
                ->setParameter('q', "%{$searchData->q}%")
            ;
        }

        if (!empty($searchData->minPrice) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.price >= :min')
                ->setParameter('min', $searchData->minPrice)
            ;
        }
        if (!empty($searchData->maxPrice) && $ignorePrice === false) {
            $query = $query
                ->andWhere('p.price <= :max')
                ->setParameter('max', $searchData->maxPrice)
            ;
        }
        if (!empty($searchData->categories)) {
            $query = $query
                ->andWhere('c.id IN (:categories)')
                ->setParameter('categories', $searchData->categories)
            ;
        }

        return $query;
    }
}
