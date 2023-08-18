<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 20/06/2019
 * Time: 13:32
 */

namespace App\Util;


class Slugger
{
    public static function slugify(string $string): string
    {
        return preg_replace('/\s+/', '-', mb_strtolower(trim(strip_tags($string)), 'UTF-8'));
    }
}