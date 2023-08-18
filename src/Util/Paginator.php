<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 20/06/2019
 * Time: 13:21
 */

namespace App\Util;


use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class Paginator
{

    private const MAX_PER_PAGE = 12;
    /**
     * @var QueryBuilder
     */
    private $builder;
    private $adapter;
    /**
     * @var int
     */
    private $maxPerPage;
    /**
     * @var int
     */
    private $currentPage;

    public function __construct(QueryBuilder $builder, $adapter, Request $request, $maxPerPage = self::MAX_PER_PAGE)
    {

        $this->builder = $builder;
        $this->adapter = $adapter;
        $this->maxPerPage = $maxPerPage;
        $this->currentPage = $request->query->getInt('page', 1);
    }

    public function paginated()
    {
        $paginator = new Pagerfanta($this->adapter);
        $paginator->setCurrentPage($this->currentPage)
            ->setMaxPerPage($this->maxPerPage);

        return $paginator;
    }
    
}