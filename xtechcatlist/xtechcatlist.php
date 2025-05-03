<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class Xtechcatlist extends Module
{
    public function __construct()
    {
        $this->name = 'xtechcatlist';
        $this->version = '1.0.1';
        $this->author = 'x-studioApp';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('X-tech categories list');
        $this->description = $this->l('This module allows displaying selected categories in a custom hook.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('header');
        
    }

    public function hookHeader(){
        if ($this->context->controller->php_self == 'index') { // Exemple pour la page d'accueil
            $this->context->controller->addCSS($this->_path . 'views/css/style.css', 'all');
        }
    }


    public function uninstall()
    {
        return parent::uninstall() && Configuration::deleteByName('XTECH_SELECTED_CATEGORIES_LIST');
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitXtechCategories')) {
            $selectedCategories = Tools::getValue('categoryBox', []);
            Configuration::updateValue('XTECH_SELECTED_CATEGORIES_LIST', json_encode($selectedCategories));
            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output . $this->renderForm();
    }

    private function renderForm()
    {
        $tree = new HelperTreeCategories('categoryTree');
        $tree->setRootCategory(Category::getRootCategory()->id)
             ->setUseCheckBox(true)
             ->setSelectedCategories(json_decode(Configuration::get('XTECH_SELECTED_CATEGORIES_LIST'), true) ?? []);

        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Select Categories'),
                    'icon'  => 'icon-list'
                ],
                'input'  => [
                    [
                        'type'  => 'categories_select',
                        'label' => $this->l('Categories'),
                        'name'  => 'categoryBox',
                        'category_tree' => $tree->render()
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right'
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitXtechCategories';
        $helper->tpl_vars = [
            'fields_value' => [],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    public function hookDisplayHome()
    {
        $selectedCategories = json_decode(Configuration::get('XTECH_SELECTED_CATEGORIES'), true) ?? [];

        $categories = [];
        if (!empty($selectedCategories)) {
            foreach ($selectedCategories as $id_category) {
                $category = new Category((int)$id_category, (int)$this->context->language->id);
                if (Validate::isLoadedObject($category)) {
                    $categories[] = [
                        'id_category' => $category->id,
                        'name'        => $category->name,
                        'image'       => $this->context->link->getCatImageLink($category->link_rewrite, $category->id),
                        'url'         => $this->context->link->getCategoryLink($category->id),
                    ];
                }
            }
        }

        $this->context->smarty->assign('xtech_categories', $categories);
        return $this->display(__FILE__, 'views/templates/hook/displayCategoryHome.tpl');
    }
}
