<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 20/06/2019
 * Time: 14:21
 */

namespace App\Util;


class TokenGenerator
{

    public static function generateToken(int $length = 60)
    {
        $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        $token = str_repeat(str_shuffle($chars), $length);
        return substr($token, 0, $length);
    }


}