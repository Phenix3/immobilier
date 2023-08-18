<?php

namespace App\Controller\Admin;

use App\Controller\Admin\BaseController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DashboardController
 * @package App\Controller\Admin
 * @Route("/admin", name="admin_dashboard_")
 */
class DashboardController extends BaseController
{
    /**
     * @Route("/", name="index", options = {
     *      "breadcrumb" = {
     *          "label" = "Dashboard"
     *      }
     * })
     */
    public function index()
    {
        $this->pageManager
            ->setVar('page_title', 'Dashboard')
            ->setVar('page_icon', 'fas fa-chronometer')
            ->setVar('page_description', '');
        return $this->render('admin/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
