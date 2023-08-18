<?php

namespace App\Controller\Admin;

use App\Controller\Admin\BaseController;
use App\Entity\Image;
use App\Entity\Property;
use App\Form\PropertyType;
use App\Repository\ImageRepository;
use App\Repository\PropertyRepository;
use App\Util\TokenGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PropertyController
 * @package App\Controller\Admin
 * @Route("/admin/property", name="admin_property_")
 */
class PropertyController extends BaseController
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
     * @Route("/{id}/edit", name="edit")
     * @param Property $property
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function edit(Property $property, Request $request, ImageRepository $imageRepository): Response
    {

        $images = $property->getImages();
        $manager = $this->getDoctrine()->getManager();
        foreach ($images as $image) {
            $image->setSessionIndex($this->session->get('index'));
            $manager->persist($image);
        }
        $manager->flush();
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $property = $this->asssociateImages($property, $imageRepository);
            $property->setProprietary($this->getUser());
            $manager->persist($property);
            $manager->flush();
            $this->addFlash('success', 'Le bien a bien été modifié');

            return $this->redirectToRoute('admin_property_index');
        }
        return $this->render('admin/property/edit.html.twig', [
            'form' => $form->createView(),
            'property' => $property
        ]);
    }

    /**
     * @Route("/new", name="new", options = {
     *      "breadcrumb" = {
     *          "label" = "Create new property",
     *          "parent_route" = "admin_property_index"
     *      }
     * })
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function new(Request $request, EntityManagerInterface $manager, ImageRepository $imageRepository): Response
    {
        $property = new Property();
        $property->setProprietary($this->getUser());
        $form = $this->createForm(PropertyType::class, $property);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $property->setProprietary($this->getUser());
            $property = $this->asssociateImages($property, $imageRepository);
            $manager->persist($property);
            $manager->flush();
            $this->addFlash('success', 'Le bien a bien été crée');

            return $this->redirectToRoute('admin_property_index');
        }
        $this->pageManager
            ->setVar('page_title', 'Property index')
            ->setVar('page_icon', 'fa fa-edit')
            ->setVar('page_description', '');
        return $this->render('admin/property/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     * @param Property $property
     * @return Response
     */
    public function show(Property $property): Response
    {
        return $this->render('admin/property/show.html.twig', compact('property'));
    }

    /**
     * @Route("/", name="index", options = {
     *      "breadcrumb" = {
     *          "label" = "Properties",
     *          "parent_route" = "admin_dashboard_index"
     *      }
     *  })
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $properties = $paginator
            ->paginate(
                $this->repository->findAllQuery(),
                $request->query->getInt('page', 1),
                12
            );
        $this->pageManager
            ->setVar('page_title', 'Property Index')
            ->setVar('page_icon', 'fa fa-box')
            ->setVar('page_description', '');
        return $this->render('admin/property/index.html.twig', compact('properties'));
    }

    /**
     * @param Property $property
     * @param Request $request
     * @return RedirectResponse
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Property $property, Request $request): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->get('_token'))) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($property);
            $manager->flush();
            $this->addFlash('success', 'Le bien a bien été supprimé');
        }
        return $this->redirectToRoute('admin_property_index');
    }

    /**
     * @param Property $property
     * @param ImageRepository $imageRepository
     * @return Property
     */
    public function asssociateImages(Property $property, ImageRepository $imageRepository): Property
    {
        if ($this->session->has('index')) {
            $index = $this->session->get('index');
            $images = $imageRepository->findBy(['sessionIndex' => $index]);
            foreach ($images as $image) {
                $image->setProperty($property)
                    ->setSessionIndex(null)
                ;
                $this->getDoctrine()->getManager()->persist($image);
            }
        } else {
            $this->session->set('index', TokenGenerator::generateToken(10));
        }

        return $property;
    }
}
