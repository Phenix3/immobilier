<?php
/**
 * Created by PhpStorm.
 * User: IBM-PC
 * Date: 21/04/2020
 * Time: 09:08
 */

namespace App\Data;


use App\Entity\Category;

class SearchData
{
    /**
     * @var string
     */
    public $q = '';

    /**
     * @var int
     */
    public $page = 1;

    /**
     * @var Category[]
     */
    public $categories = [];

    /**
     * @var integer|null
     */
    public $maxPrice;

    /**
     * @var integer|null
     */
    public $minPrice;
}