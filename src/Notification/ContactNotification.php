<?php
/**
 * Created by PhpStorm.
 * User: IBM-Phenix
 * Date: 22/06/2019
 * Time: 10:05
 */

namespace App\Notification;


use App\Entity\Contact;

class ContactNotification extends Notification
{

    public function notify(Contact $contact)
    {
        $message = (new \Swift_Message($contact->getSubject()))
            ->setFrom($contact->getEmail())
            ->setTo('phenixibm@gmail.com')
            ->setBody($this->environment->render('emails/contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');

        $this->mailer->send($message);
    }

}