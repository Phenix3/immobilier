<?php
/**
 * Created by PhpStorm.
 * User: IBM-PC
 * Date: 24/07/2020
 * Time: 09:25
 */

namespace App\Controller\Admin;


use App\Service\PageManagerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{

    /**
     * @var PageManager
     */
    public $pageManager;

    /**
     * @param PageManager $pageManager
     * @required
     */
    public function setPageManager(PageManagerService $pageManager)
    {
        $this->pageManager = $pageManager;
    }

}