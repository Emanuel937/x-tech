<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class XtechIcons extends Module
{
    const UPLOAD_DIR = '/uploads/';
    const TABLE_NAME = 'xtech_icons';
    const MODULE_VERSION = '1.0.2';

    public function __construct()
    {
        $this->name = 'xtechicons';
        $this->version = self::MODULE_VERSION;
        $this->author = 'X-Tech';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->trans('X-Tech Icons', [], 'Modules.Xtechicons.Admin');
        $this->description = $this->trans(
            'Module to manage and display custom icons with title, description, and optional URL.',
            [],
            'Modules.Xtechicons.Admin'
        );

        $this->checkForUpgrades();
    }
  
    public function install()
    {
        $success = parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayFooter')
            && $this->registerHook('header')
            && $this->createUploadDirectory()
            && $this->createTable();

        if ($success) {
            $this->upgradeTo10('1.0.0');
            Configuration::updateValue('XTECHICONS_VERSION', self::MODULE_VERSION);
        }

        return $success;
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->dropTable();
    }

    public function hookHeader()
    {
        if ($this->context->controller->php_self == 'index') { 
            $this->context->controller->addCSS($this->_path . 'views/css/style.css', 'all');
        }
    }

    private function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . self::TABLE_NAME . "` (
            `id_icon` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `image` VARCHAR(255) NOT NULL,
            `url` VARCHAR(255) DEFAULT NULL,
            `position` INT(11) DEFAULT 0,
            `active` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id_icon`)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return Db::getInstance()->execute($sql);
    }

    private function dropTable()
    {
        return Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . self::TABLE_NAME . "`");
    }

    private function createUploadDirectory()
    {
        $uploadDir = _PS_MODULE_DIR_ . $this->name . self::UPLOAD_DIR;
        if (!file_exists($uploadDir)) {
            return mkdir($uploadDir, 0755, true);
        }
        return true;
    }

    public function upgradeTo10($old_version)
    {
        $db = Db::getInstance();
        
        $position_exists = $db->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . self::TABLE_NAME . "` LIKE 'position'");
        $active_exists = $db->executeS("SHOW COLUMNS FROM `" . _DB_PREFIX_ . self::TABLE_NAME . "` LIKE 'active'");
        
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . self::TABLE_NAME . "`";
        $alter_statements = [];
        
        if (empty($position_exists)) {
            $alter_statements[] = "ADD COLUMN `position` INT(11) DEFAULT 0 AFTER `url`";
        }
        if (empty($active_exists)) {
            $alter_statements[] = "ADD COLUMN `active` TINYINT(1) DEFAULT 1 AFTER `position`";
        }
        
        if (!empty($alter_statements)) {
            $sql .= " " . implode(", ", $alter_statements);
            return $db->execute($sql);
        }
        
        return true;
    }

    private function checkForUpgrades()
    {
        $installed_version = Configuration::get('XTECHICONS_VERSION');
        if (!$installed_version) {
            Configuration::updateValue('XTECHICONS_VERSION', self::MODULE_VERSION);
            return;
        }

        if (version_compare($installed_version, self::MODULE_VERSION, '<')) {
            $this->upgradeTo10($installed_version);
            Configuration::updateValue('XTECHICONS_VERSION', self::MODULE_VERSION);
        }
    }

    public function getContent()
    {
        $output = '';
        $id_icon = (int)Tools::getValue('id_icon');

        try {
            if (Tools::isSubmit('submitXtechIcons')) {
                if ($id_icon) {
                    if ($this->updateIcon($id_icon)) {
                        $output .= $this->displayConfirmation($this->trans('Icon updated successfully', [], 'Modules.Xtechicons.Admin'));
                        $id_icon = 0; // Reset after successful update
                    } else {
                        $output .= $this->displayError($this->trans('Error updating icon', [], 'Modules.Xtechicons.Admin'));
                    }
                } else {
                    if ($this->saveIcon()) {
                        $output .= $this->displayConfirmation($this->trans('Icon saved successfully', [], 'Modules.Xtechicons.Admin'));
                    } else {
                        $output .= $this->displayError($this->trans('Error saving icon', [], 'Modules.Xtechicons.Admin'));
                    }
                }
            }

            if (Tools::isSubmit('deleteIcon') && ($delete_id = (int)Tools::getValue('id_icon'))) {
                if ($this->deleteIcon($delete_id)) {
                    $output .= $this->displayConfirmation($this->trans('Icon deleted successfully', [], 'Modules.Xtechicons.Admin'));
                    $id_icon = 0; // Reset form if we were editing the deleted icon
                } else {
                    $output .= $this->displayError($this->trans('Error deleting icon', [], 'Modules.Xtechicons.Admin'));
                }
            }
        } catch (Exception $e) {
            $output .= $this->displayError($e->getMessage());
        }

        return $output . $this->renderForm($id_icon) . $this->renderIconsList();
    }

    private function saveIcon()
    {
        $title = trim(Tools::getValue('title'));
        $description = trim(Tools::getValue('description'));
        $url = trim(Tools::getValue('url'));
        
        if (empty($title)) {
            throw new Exception($this->trans('Title is required', [], 'Modules.Xtechicons.Admin'));
        }

        if (!$this->validateUrl($url)) {
            throw new Exception($this->trans('Invalid URL format', [], 'Modules.Xtechicons.Admin'));
        }

        $imagePath = $this->uploadImage();
        if (!$imagePath) {
            throw new Exception($this->trans('Image upload failed', [], 'Modules.Xtechicons.Admin'));
        }

        return Db::getInstance()->insert(self::TABLE_NAME, [
            'title' => pSQL($title),
            'description' => pSQL($description),
            'image' => pSQL($imagePath),
            'url' => $url ? pSQL($url) : null,
        ]);
    }

    private function updateIcon($id_icon)
    {
        $title = trim(Tools::getValue('title'));
        $description = trim(Tools::getValue('description'));
        $url = trim(Tools::getValue('url'));
        
        if (empty($title)) {
            throw new Exception($this->trans('Title is required', [], 'Modules.Xtechicons.Admin'));
        }

        if (!$this->validateUrl($url)) {
            throw new Exception($this->trans('Invalid URL format', [], 'Modules.Xtechicons.Admin'));
        }

        $data = [
            'title' => pSQL($title),
            'description' => pSQL($description),
            'url' => $url ? pSQL($url) : null,
        ];

        $imagePath = $this->uploadImage();
        if ($imagePath) {
            $old_image = Db::getInstance()->getValue(
                'SELECT image FROM ' . _DB_PREFIX_ . self::TABLE_NAME . ' WHERE id_icon = ' . (int)$id_icon
            );
            if ($old_image && file_exists(_PS_ROOT_DIR_ . $old_image)) {
                unlink(_PS_ROOT_DIR_ . $old_image);
            }
            $data['image'] = pSQL($imagePath);
        }

        return Db::getInstance()->update(self::TABLE_NAME, $data, 'id_icon = ' . (int)$id_icon);
    }

    private function deleteIcon($id_icon)
    {
        $image = Db::getInstance()->getValue(
            'SELECT image FROM ' . _DB_PREFIX_ . self::TABLE_NAME . ' WHERE id_icon = ' . (int)$id_icon
        );
        
        if ($image && file_exists(_PS_ROOT_DIR_ . $image)) {
            unlink(_PS_ROOT_DIR_ . $image);
        }

        return Db::getInstance()->delete(self::TABLE_NAME, 'id_icon = ' . (int)$id_icon);
    }

    private function uploadImage()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024;

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
        
        if (!in_array($mimeType, $allowedTypes) || $_FILES['image']['size'] > $maxSize) {
            return false;
        }

        $imageName = uniqid('icon_') . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $destination = _PS_MODULE_DIR_ . $this->name . self::UPLOAD_DIR . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            return $this->_path . 'uploads/' . $imageName;
        }

        return false;
    }

    private function validateUrl($url)
    {
        return empty($url) || filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function renderForm($id_icon = 0)
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->submit_action = 'submitXtechIcons';

        if ($id_icon) {
            $icon = Db::getInstance()->getRow(
                'SELECT * FROM ' . _DB_PREFIX_ . self::TABLE_NAME . ' WHERE id_icon = ' . (int)$id_icon
            );
            $helper->fields_value = [
                'id_icon' => $id_icon,
                'title' => $icon['title'],
                'description' => $icon['description'],
                'url' => $icon['url'],
                'image' => ''
            ];
            $helper->title = $this->trans('Edit Icon', [], 'Modules.Xtechicons.Admin');
        } else {
            $helper->fields_value = [
                'id_icon' => 0,
                'title' => Tools::getValue('title', ''),
                'description' => Tools::getValue('description', ''),
                'url' => Tools::getValue('url', ''),
                'image' => ''
            ];
            $helper->title = $this->trans('Add New Icon', [], 'Modules.Xtechicons.Admin');
        }

        $fields_form = [
            'form' => [
                'legend' => ['title' => $helper->title],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_icon'
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Title', [], 'Modules.Xtechicons.Admin'),
                        'name' => 'title',
                        'required' => true,
                        'class' => 'form-control'
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->trans('Description', [], 'Modules.Xtechicons.Admin'),
                        'name' => 'description',
                        'class' => 'form-control'
                    ],
                    [
                        'type' => 'file',
                        'label' => $this->trans('Image', [], 'Modules.Xtechicons.Admin'),
                        'name' => 'image',
                        'required' => !$id_icon,
                        'desc' => $id_icon ? $this->trans('Leave blank to keep existing image', [], 'Modules.Xtechicons.Admin') : ''
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('URL (optional)', [], 'Modules.Xtechicons.Admin'),
                        'name' => 'url',
                        'class' => 'form-control'
                    ]
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.Xtechicons.Admin'),
                    'class' => 'btn btn-default pull-right'
                ],
                'buttons' => [
                    [
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->trans('Back to list', [], 'Admin.Actions'),
                        'icon' => 'process-icon-back'
                    ]
                ]
            ]
        ];

        return $helper->generateForm([$fields_form]);
    }

    private function renderIconsList()
    {
        $icons = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::TABLE_NAME . ' ORDER BY position, id_icon'
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_icon';
        $helper->actions = ['edit', 'delete'];
        $helper->show_toolbar = true;
        $helper->title = $this->trans('Saved Icons', [], 'Modules.Xtechicons.Admin');
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $fields_list = [
            'image' => [
                'title' => $this->trans('Image', [], 'Modules.Xtechicons.Admin'),
                'type' => 'text',
                'callback' => 'displayImage',
                'callback_object' => $this,
            ],
            'title' => [
                'title' => $this->trans('Title', [], 'Modules.Xtechicons.Admin'),
                'type' => 'text',
            ],
            'description' => [
                'title' => $this->trans('Description', [], 'Modules.Xtechicons.Admin'),
                'type' => 'text',
            ],
            'url' => [
                'title' => $this->trans('URL', [], 'Modules.Xtechicons.Admin'),
                'type' => 'text',
            ]
        ];

        return $helper->generateList($icons, $fields_list);
    }

    public static function displayImage($value)
    {
        return '<img src="' . $value . '" style="max-width: 50px; max-height: 50px;" />';
    }

    public function hookDisplayHome()
    {
        return $this->displayIcons();
    }

    public function hookDisplayFooter()
    {
        return $this->displayIcons();
    }

    private function displayIcons()
    {
        $icons = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . self::TABLE_NAME . ' WHERE active = 1 ORDER BY position, id_icon'
        );
        
        $this->context->smarty->assign([
            'xtech_icons' => $icons,
            'module_dir' => $this->_path
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/displayIcons.tpl');
    }
}