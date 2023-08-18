<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 19/06/2019
 * Time: 18:04
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


class PropertySearch
{


    /**
     * @var int|null
     */
    private $maxPrice;

    /**
     * @var ArrayCollection
     */
    private $tags;

    /**
     * @var int|null
     * @Assert\Range(min="10", max="400")
     */
    private $minSurface;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getMaxPrice(): ?int
    {
        return $this->maxPrice;
    }

    /**
     * @param int|null $maxPrice
     * @return PropertySearch
     */
    public function setMaxPrice(int $maxPrice): PropertySearch
    {
        $this->maxPrice = $maxPrice;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMinSurface(): ?int
    {
        return $this->minSurface;
    }

    /**
     * @param int|null $minSurface
     * @return PropertySearch
     */
    public function setMinSurface(int $minSurface): PropertySearch
    {
        $this->minSurface = $minSurface;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags(): ArrayCollection
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     * @return PropertySearch
     */
    public function setTags(ArrayCollection $tags): PropertySearch
    {
        $this->tags = $tags;
        return $this;
    }


}