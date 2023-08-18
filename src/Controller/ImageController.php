<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use App\Repository\PropertyRepository;
use App\Util\TokenGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends AbstractController
{

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        if (!$this->session->has('index')) {
            $this->session->set('index', TokenGenerator::generateToken(10));
        }
    }

    /**
     * @Route("/", name="image_index", methods={"GET"})
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/form", name="image_form")
     */
    public function form()
    {
        return $this->render('image/_form.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/save", name="image_save")
     */
    public function save(Request $request): JsonResponse
    {
        $images_path = $this->getParameter('public_path').'/images/properties';
        $thumbs_path = $this->getParameter('public_path').'/images/properties/thumbs';

        $files = [];
        $filesBag = $request->files->all();

        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($filesBag), \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($fileIterator as $file) {
            if (is_array($file) || null === $file) {
                continue;
            }
            $files[] = $file;
        }

        if (!is_dir($images_path)) {
            mkdir($images_path);
        }
        if (is_dir($thumbs_path)) {
            mkdir($thumbs_path);
        }
        $manager = $this->getDoctrine()->getManager();
        foreach ($files as $file) {
            $file_name = TokenGenerator::generateToken(60);
            $save_name = $file_name.'.'.$file->getClientOriginalExtension();
            $file->move($images_path, $save_name);

            $image = (new Image())
                ->setUrl($save_name)
                ->setOriginalName(basename($file->getClientOriginalName()))
                ->setSessionIndex($this->session->get('index'));

            $manager->persist($image);
        }
        $manager->flush();
        return JsonResponse::create(['message' => 'Success']);
    }

    /**
     * @Route("/delete", name="image_delete")
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @param ObjectManager $manager
     * @return JsonResponse
     */
    public function delete(Request $request, ImageRepository $imageRepository): JsonResponse
    {
        $uploaded_file = $imageRepository->findOneBy(['originalName' => basename($request->get('name'))]);
        if ($uploaded_file) {
            $file_path = $this->getParameter('public_path').'/images/properties/'.$uploaded_file->getUrl();
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($uploaded_file);
            $manager->flush();
        }

        return JsonResponse::create([]);
    }

    /**
     * @Route("/server", name="image_server")
     * @param ImageRepository $imageRepository
     * @return JsonResponse
     */
    public function server(ImageRepository $imageRepository)
    {
        if ($this->session->has('index'))
        {
            $index = $this->session->get('index');
            $images = $imageRepository->findBy(['sessionIndex' => $index]);
            foreach ($images as $image) {
                $file = new File($this->getParameter('public_path').'/images/properties/'.$image->getUrl());
                $imageAnswer[] = [
                    'original' => $image->getOriginalName(),
                    'server' => $image->getUrl(),
                    'size' => $file->getSize()
                ];
            }
            if (!empty($imageAnswer)) {
                return JsonResponse::create(['images' => $imageAnswer]);
            }
        }

        return JsonResponse::create([]);
    }
}
