<?php
/**
 * Módulo productbadges para PrestaShop 1.7
 * Permite gestionar etiquetas visuales reutilizables para productos
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/ProductBadge.php';

class ProductBadges extends Module
{
    public function __construct()
    {
        $this->name          = 'productbadges';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'David Fernández';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Product Badges');
        $this->description = $this->l('Gestiona etiquetas visuales reutilizables para productos.');
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '1.7.99.99',
        ];
    }

    /**
     * Instalación del módulo:
     * - Crea las tablas SQL
     * - Registra los hooks necesarios
     * - Registra la pestaña en el back office
     * - Guarda la configuración por defecto
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!$this->executeSqlFile(_PS_MODULE_DIR_ . $this->name . '/sql/install.php')) {
            return false;
        }

        // Hooks reales del tema Classic para listados en PrestaShop 1.7.8.x
        $hooks = [
            'displayProductListingHook',
            'displayProductAdditionalInfo',
            'displayHeader',
        ];
        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        if (!$this->installTab()) {
            return false;
        }

        Configuration::updateValue('PRODUCTBADGES_ENABLED', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_LISTING', 1);
        Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', 1);
        Configuration::updateValue('PRODUCTBADGES_MAX_BADGES', 3);

        return true;
    }

    /**
     * Desinstalación del módulo:
     * Primero limpiamos nuestras cosas y al final llamamos a parent::uninstall()
     * Así controlamos el orden de limpieza correctamente
     */
    public function uninstall()
    {
        if (!$this->executeSqlFile(_PS_MODULE_DIR_ . $this->name . '/sql/uninstall.php')) {
            return false;
        }

        if (!$this->uninstallTab()) {
            return false;
        }

        Configuration::deleteByName('PRODUCTBADGES_ENABLED');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_LISTING');
        Configuration::deleteByName('PRODUCTBADGES_SHOW_PRODUCT');
        Configuration::deleteByName('PRODUCTBADGES_MAX_BADGES');

        return parent::uninstall();
    }

    /**
     * Registra la pestaña AdminProductBadges en el menú lateral del back office
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active      = 1;
        $tab->class_name  = 'AdminProductBadges';
        $tab->module      = $this->name;
        $tab->id_parent   = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->icon        = 'label';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Product Badges');
        }

        return $tab->add();
    }

    /**
     * Elimina la pestaña AdminProductBadges del back office
     */
    private function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminProductBadges');
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }
        return true;
    }

    /**
     * Ejecuta un archivo PHP que contiene un array $sql de queries
     */
    private function executeSqlFile($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        return (bool) include $filepath;
    }

    /**
     * Página de configuración del módulo en el back office
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_productbadges_config')) {
            // Doble cast (int)(bool) para forzar que los switches solo sean 0 o 1
            $enabled     = (int) (bool) Tools::getValue('PRODUCTBADGES_ENABLED');
            $showListing = (int) (bool) Tools::getValue('PRODUCTBADGES_SHOW_LISTING');
            $showProduct = (int) (bool) Tools::getValue('PRODUCTBADGES_SHOW_PRODUCT');
            $maxBadges   = (int) Tools::getValue('PRODUCTBADGES_MAX_BADGES');

            if ($maxBadges < 1 || $maxBadges > 10) {
                $output .= $this->displayError($this->l('El número máximo de badges debe estar entre 1 y 10.'));
            } else {
                Configuration::updateValue('PRODUCTBADGES_ENABLED', $enabled);
                Configuration::updateValue('PRODUCTBADGES_SHOW_LISTING', $showListing);
                Configuration::updateValue('PRODUCTBADGES_SHOW_PRODUCT', $showProduct);
                Configuration::updateValue('PRODUCTBADGES_MAX_BADGES', $maxBadges);
                $output .= $this->displayConfirmation($this->l('Configuración guardada correctamente.'));
            }
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Renderiza el formulario de configuración global usando HelperForm
     */
    private function renderConfigForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuración general'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'   => 'switch',
                        'label'  => $this->l('Activar módulo'),
                        'name'   => 'PRODUCTBADGES_ENABLED',
                        'values' => [
                            ['id' => 'enabled_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'   => 'switch',
                        'label'  => $this->l('Mostrar en listados'),
                        'name'   => 'PRODUCTBADGES_SHOW_LISTING',
                        'values' => [
                            ['id' => 'listing_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'listing_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'   => 'switch',
                        'label'  => $this->l('Mostrar en ficha de producto'),
                        'name'   => 'PRODUCTBADGES_SHOW_PRODUCT',
                        'values' => [
                            ['id' => 'product_on',  'value' => 1, 'label' => $this->l('Sí')],
                            ['id' => 'product_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Número máximo de badges por producto'),
                        'name'  => 'PRODUCTBADGES_MAX_BADGES',
                        'class' => 'fixed-width-xs',
                        'desc'  => $this->l('Entre 1 y 10.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Guardar'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module          = $this;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->title           = $this->displayName;
        $helper->submit_action   = 'submit_productbadges_config';

        $helper->fields_value['PRODUCTBADGES_ENABLED']      = (int) Configuration::get('PRODUCTBADGES_ENABLED');
        $helper->fields_value['PRODUCTBADGES_SHOW_LISTING'] = (int) Configuration::get('PRODUCTBADGES_SHOW_LISTING');
        $helper->fields_value['PRODUCTBADGES_SHOW_PRODUCT'] = (int) Configuration::get('PRODUCTBADGES_SHOW_PRODUCT');
        $helper->fields_value['PRODUCTBADGES_MAX_BADGES']   = (int) Configuration::get('PRODUCTBADGES_MAX_BADGES');

        return $helper->generateForm([$fields_form]);
    }


    // HOOKS 

    public function hookDisplayProductListingHook($params)
    {

    }

    public function hookDisplayProductAdditionalInfo($params)
    {

    }

    public function hookDisplayHeader($params)
    {

    }
}
