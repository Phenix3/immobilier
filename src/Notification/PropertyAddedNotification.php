<?php


namespace App\Notification;


use App\Entity\Property;
use Symfony\Component\Security\Core\User\UserInterface;

class PropertyAddedNotification extends Notification
{

    public function notify(UserInterface $user, Property $property)
    {
        $message = (new \Swift_Message('New property added.'))
            ->setFrom($user->getEmail())
            ->setTo('phenixibm@gmail.com')
            ->setBody($this->environment->render('emails/proeprty_added.html.twig', [
                'user' => $user,
                'property' => $property
            ]), 'text/html');

        $this->mailer->send($message);
    }

}