<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Category;
use App\Entity\Contact;
use App\Entity\Property;
use App\Entity\PropertySearch;
use App\Form\ContactType;
use App\Form\PropertySearchType;
use App\Form\PropertyType;
use App\Form\SearchForm;
use App\Notification\ContactNotification;
use App\Repository\ImageRepository;
use App\Repository\PropertyRepository;
use App\Util\TokenGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PropertyController
 * @package App\Controller
 * @Route("/property")
 */
class PropertyController extends AbstractController
{

    /**
     * @var PropertyRepository
     */
    private $repository;
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(PropertyRepository $repository, SessionInterface $session)
    {
        $this->repository = $repository;
        $this->session = $session;
        if (!$this->session->has('index')) {
            $this->session->set('index', TokenGenerator::generateToken(10));
        }
    }

    /**
     * @Route("/{slug}-{id}", name="property_show", requirements={"slug"="[a-z0-9\.-]+", "id"="[0-9]+"})
     * @param Property $property
     * @param Request $request
     * @param string $slug
     * @param ContactNotification $notification
     * @return Response
     */
    public function show(Property $property, Request $request, string $slug, ContactNotification $notification): Response
    {
        $this->denyAccessUnlessGranted('PROPERTY_VIEW', $property);

        $contact = new Contact();

        $formContact = $this->createForm(ContactType::class, $contact);

        $formContact->handleRequest($request);

        if ($formContact->isSubmitted() && $formContact->isValid()) {
            $contact->setProperty($property);
            $notification->notify($contact);
            $this->addFlash('success', 'Votre message a bien été envoyé.');

            return $this->redirectToRoute('property_show', [
                'id' => $property->getId(),
                'slug' => $property->getSlug()
            ], 301);
        }

        if (!$property->getSlug() === $slug) {
            return $this->redirectToRoute('property_show', [
                'id' => $property->getId(),
                'slug' => $property->getSlug()
            ], 301);
        }
        return $this->render('property/show.html.twig', [
            'property' => $property,
            'formContact' => $formContact->createView()
        ]);
    }


    /**
     * @Route("/purshase", name="property_purshase", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * @param Request $request
     * @return Response
     */
    public function purshase(Request $request): Response
    {
        $property = new Property();
        $property->setProprietary($this->getUser());
        $propertyForm = $this->createForm(PropertyType::class, $property);
        $propertyForm->handleRequest($request);
        if ($propertyForm->isSubmitted() && $propertyForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $property
                ->setIsPublished(false)
                ->setSold(false);
            $manager->persist($property);
            $manager->flush();

            $this->addFlash('success', 'alerts.property_added');
            return $this->redirectToRoute('home');
        }
        return $this->render('property/purshase.html.twig', [
            'propertyForm' => $propertyForm->createView()
        ]);
    }


    /**
     * @Route("/new", name="property_new", methods={"GET", "POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @param Request $request
     * @param ObjectManager $manager
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function new(Request $request, ImageRepository $imageRepository): Response
    {
        $property = new Property();
        //$property->setProprietary($this->getUser());
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $property = $this->asssociateImages($property, $imageRepository);
            $property->setProprietary($this->getUser());
            $manager->persist($property);
            $manager->flush();

            $this->addFlash('success', 'alerts.property.created');
            return $this->redirectToRoute('home');
        }

        return $this->render('property/new.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/{categorySlug}", name="property_index", requirements={"slug"="[a-z0-9\.-]+"}, defaults={"categorySlug"=null})
     * @ParamConverter("category", options={"mapping": {"categorySlug": "slug"}})
     * @param Category $category
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator, Category $category = null): Response
    {
//        $search = new PropertySearch();
//        $form = $this->createForm(PropertySearchType::class, $search);
//        $form->handleRequest($request);
        $searchData = new SearchData();
        $searchData->page = $request->query->getInt('page', 1);
        $searchDataForm = $this->createForm(SearchForm::class, $searchData);
        $searchDataForm->handleRequest($request);
        [$min, $max] = $this->repository->findMinMax($searchData);
        $properties = $this->repository->findSearch($searchData);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'content' => $this->renderView('property/_properties.html.twig', ['properties' => $properties]),
                'sorting' => $this->renderView('property/_sorting.html.twig', ['properties' => $properties]),
                'pagination' => $this->renderView('property/_pagination.html.twig', ['properties' => $properties]),
            ]);
        }

        return $this->render('property/index.html.twig', [
            'properties' => $properties,
            'form' => $searchDataForm->createView(),
            'min' => $min,
            'max' => $max
        ]);
    }

    public function getAdvancedSearchForm()
    {
        $propertySearch = new PropertySearch();
        $advancedSearchForm = $this->createForm(PropertySearchType::class, $propertySearch);
        return $this->render('property/_advanced_search.html.twig.html.twig', [
            'advancedSearchForm' => $advancedSearchForm->createView()
        ]);
    }

    public function asssociateImages(Property $property, ImageRepository $imageRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        if ($this->session->has('index')) {
            $index = $this->session->get('index');
            $images = $imageRepository->findBy(['sessionIndex' => $index]);
            foreach ($images as $image) {
                $image->setProperty($property)
                    ->setSessionIndex(null);
                $manager->persist($image);
            }
        } else {
            $this->session->set('index', TokenGenerator::generateToken(10));
        }

        return $property;
    }
}
