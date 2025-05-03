<?php
ini_set('display_errors', 1);

// Définit le niveau de rapport d'erreurs à E_ALL (toutes les erreurs)
error_reporting(E_ALL);

// Exemple de code PrestaShop

class SitefixCatalogueDeleteCatModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $id         = Tools::getValue("ID") ?: false;
        $file_path  = Tools::getValue("FILE_URL");
        $response   = [];

        if ($id) {
            $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'sitefix_catalogue WHERE catalog_id = ' . (int)$id;
            if (Db::getInstance()->execute($sql)) {
                $response['status'] = 'success';
                $response['message'] = 'Data is deleted';
                
                // Delete file if it exists
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to delete data';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid ID';
        }

        // Assigner les variables Smarty
 $this->addCSS(array(
    'path' => 'themes/AngarTheme/assets/css/angartheme.css',
    'media' => 'all', // Assurez-vous que ce média correspond au fichier CSS
));
        $this->context->smarty->assign([
            "catalogs"       => $this->showAllCatalog(),
            "brands"         => $this->getBrands(),
            "selected_brand" => $this->getSelected(),
            "layout"         => $this->getLayout(),
            "stylesheets"    => $this->getStylesheets(),
            "javascript"     => $this->getJavascript(),
            "js_custom_vars" => Media::getJsDef(),
            "notifications"  => $this->prepareNotifications(),
	    "controller_url" => Context::getContext()->link->getModuleLink("sitefixCatalogue", 'deleteCat')
        
	    
        ]);

        // Charger le layout du thème
        $templatePath = _PS_MODULE_DIR_ . 'sitefixCatalogue/views/templates/front/default.tpl';
        
        if (file_exists($templatePath)) {
            // Récupérer le contenu du template
            $templateContent = $this->context->smarty->fetch($templatePath);
            
            // Afficher le contenu
            echo $templateContent;
        } else {
            throw new PrestaShopException('Le template default.tpl n\'existe pas dans votre module.');
        }
    }

    protected function isAjaxRequest()
    {
        // Vérifie si l'en-tête HTTP X-Requested-With est défini et égal à XMLHttpRequest
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function showAllCatalog()
    {
        $brands = Tools::getValue("brands");
        if (isset($brands) && !empty($brands)) {
            $brands = pSQL($brands);
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'sitefix_catalogue` WHERE brand = "' . $brands . '"';

        } else {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'sitefix_catalogue`';


        }
        return Db::getInstance()->executeS($sql);
    }

    public function getBrands()
    {
        $brands_array = [];
        $brands = Db::getInstance()->executeS('
            SELECT id_manufacturer AS id_brand, name
            FROM ' . _DB_PREFIX_ . 'manufacturer
            ORDER BY name ASC
        ');
        foreach ($brands as $brand) {
            $brands_array[] = [
                "id"   => $brand['id_brand'],
                "name" => $brand['name'],
            ];
        }

        return $brands_array;
    }

    public function getSelected()
    {
        $brands = Db::getInstance()->executeS('
            SELECT DISTINCT brand AS brandsID
            FROM ' . _DB_PREFIX_ . 'sitefix_catalogue
        ');
        return $brands;
    }
}
?>
