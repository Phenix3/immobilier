<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 22/06/2019
 * Time: 10:06
 */

namespace App\Notification;


use Twig\Environment;

class Notification
{

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var Environment
     */
    protected $environment;

    public function __construct(\Swift_Mailer $mailer, Environment $environment)
    {

        $this->mailer = $mailer;
        $this->environment = $environment;
    }

}