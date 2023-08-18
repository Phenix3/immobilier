<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 18/06/2019
 * Time: 17:22
 */

namespace App\Notification;


use App\Entity\User;
use Twig\Environment;

class EmailConfirmationNotification
{
    /**
     * @var Environment
     */
    private $environment;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(\Swift_Mailer $mailer, Environment $environment)
    {

        $this->environment = $environment;
        $this->mailer = $mailer;
    }

    public function notify(User $user)
    {
        $message = (new \Swift_Message('Ma super Agence'))
            ->setFrom($user->getEmail())
            ->setTo('confirm@agence.fr')
            ->setBody($this->environment->render('emails/confirm.html.twig', [
                'user' => $user
            ]), 'text/html');
        $this->mailer->send($message);
    }
}