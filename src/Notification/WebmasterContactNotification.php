<?php

namespace App\Notification;

use App\Entity\Contact;

class WebmasterContactNotification extends Notification
{

    public function notify(Contact $contact)
    {
        $message = (new \Swift_Message($contact->getSubject()))
            ->setFrom($contact->getEmail())
            ->setTo('phenixibm@gmail.com')
            ->setBody($this->environment->render('emails/webmaster_contact.html.twig', [
                'contact' => $contact
            ]), 'text/html');

        $this->mailer->send($message);
    }
}
