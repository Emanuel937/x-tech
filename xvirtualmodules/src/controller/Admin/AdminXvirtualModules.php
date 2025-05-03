<?php

namespace XvirtualModules\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;

class AdminXvirtualModules extends FrameworkBundleAdminController
{
    public function indexAction(): Response
    {
        return $this->render('@Modules/xvirtualmodules/views/templates/admin/index.html.twig');
    }
}