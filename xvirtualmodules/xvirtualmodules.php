<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class XvirtualModules extends Module
{
    public function __construct()
    {
        $this->name = 'xvirtualmodules';
        $this->tab = 'front_office_features';
        $this->version = '1.0.7';
        $this->author = 'X-studioApp | Emanuel ABIZIMI';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('xVirtual Modules');
        $this->description = $this->l('This module allows you to set up a virtual module that displays products and banners using the layout you want.');
    }

    public function install()
    {
        return parent::install() && $this->registerTab();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->unregisterTab();
    }

    public function registerTab()
    {
        $parentTab = new Tab();
        $parentTab->active = 1;
        $parentTab->class_name = 'AdminXvirtualModules';
        $parentTab->module = $this->name;
        $parentTab->id_parent = 0;
        $parentTab->name = [];
        $parentTab->icon = 'cogs';
        $parentTab->route_name = 'admin_xvirtualmodules_index';

        foreach (Language::getLanguages() as $lang) {
            $parentTab->name[$lang['id_lang']] = 'Xvirtual Modules';
        }

        return $parentTab->add();
    }

    public function unregisterTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminXvirtualModules');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        $container = SymfonyContainer::getInstance();
        $router = $container->get('router');
        $url = $router->generate('admin_xvirtualmodules_index');
      
        Tools::redirectAdmin($url);
        var_dump($url);
    }
}