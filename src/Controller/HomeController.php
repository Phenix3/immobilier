<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\PropertyRepository;
use App\Notification\WebmasterContactNotification;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param PropertyRepository $propertyRepository
     * @return Response
     */
    public function index(PropertyRepository $propertyRepository)
    {
        $properties = $propertyRepository->findLatests(6);
        return $this->render('home/index.html.twig', compact('properties'));
    }

    /**
     * @Route("/contact", name="home_contact")
     * @param Request $request
     * @param WebmasterContactNotification $webmasterNotification
     * @return RedirectResponse|Response
     */
    public function contact(Request $request, WebmasterContactNotification $webmasterNotification)
    {
        $contact = new Contact();
        $contactForm = $this->createForm(ContactType::class, $contact);
        $contactForm->handleRequest($request);
        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $webmasterNotification->notify($contact);
            $this->addFlash('success', 'Votre message a bien ete recu');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/contact.html.twig', [
            'contactForm' => $contactForm->createView()
        ]);
    }

    /**
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function getNav(CategoryRepository $categoryRepository)
    {
        return $this->render('partials/nav.html.twig', [
            'categories' => $categoryRepository->findAll()
        ]);
    }
}
