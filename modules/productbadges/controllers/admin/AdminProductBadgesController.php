<?php
/**
 * AdminProductBadgesController
 * Gestiona el CRUD de badges desde el back office de PrestaShop
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'productbadges/classes/ProductBadge.php';

class AdminProductBadgesController extends ModuleAdminController
{
    /** @var array Posiciones válidas para una badge */
    const VALID_POSITIONS = ['top-left', 'top-right'];

    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'productbadges';
        $this->className   = 'ProductBadge';
        $this->identifier  = 'id_badge';
        $this->lang        = true;  

        parent::__construct();

        $this->module = Module::getInstanceByName('productbadges');

        $this->fields_list = [
            'id_badge'   => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'label'      => [
                'title' => $this->l('Etiqueta'),
            ],
            'bg_color'   => [
                'title'   => $this->l('Color fondo'),
                'align'   => 'center',
                'callback' => 'renderColorSwatch',
            ],
            'text_color' => [
                'title'   => $this->l('Color texto'),
                'align'   => 'center',
                'callback' => 'renderColorSwatch',
            ],
            'position'   => [
                'title' => $this->l('Posición'),
                'align' => 'center',
            ],
            'active'     => [
                'title'   => $this->l('Activo'),
                'align'   => 'center',
                'active'  => 'status',
                'type'    => 'bool',
                'orderby' => false,
            ],
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Eliminar seleccionados'),
                'confirm' => $this->l('¿Eliminar las badges seleccionadas?'),
                'icon'    => 'icon-trash',
            ],
        ];
    }

    /**
     * Callback para renderizar un swatch de color en el listado
     * Recibe el valor del campo y muestra un bloque de color
     *
     * @param string $value  Valor hexadecimal del color
     * @return string       
     */
    public function renderColorSwatch($value)
    {
        // Validamos que sea un color hex válido antes de renderizar
        $value = Tools::safeOutput($value);
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        return '<span style="display:inline-block;width:24px;height:24px;'
            . 'background:' . $value . ';border:1px solid #ccc;border-radius:3px;" '
            . 'title="' . $value . '"></span>';
    }

    /**
     * Renderiza el formulario de creación/edición de una badge
     */
    public function renderForm()
    {
        $languages  = Language::getLanguages(false);
        $idLangDefault = (int) Configuration::get('PS_LANG_DEFAULT');

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Badge'),
                'icon'  => 'icon-tag',
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Texto de la etiqueta'),
                    'name'     => 'label',
                    'lang'     => true,
                    'required' => true,
                    'hint'     => $this->l('Máximo 60 caracteres.'),
                    'col'      => 4,
                ],
                [
                    'type'     => 'color',
                    'label'    => $this->l('Color de fondo'),
                    'name'     => 'bg_color',
                    'required' => true,
                    'col'      => 2,
                ],
                [
                    'type'     => 'color',
                    'label'    => $this->l('Color del texto'),
                    'name'     => 'text_color',
                    'required' => true,
                    'col'      => 2,
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Posición'),
                    'name'    => 'position',
                    'required'=> true,
                    'options' => [
                        'query' => [
                            ['id' => 'top-left',  'name' => $this->l('Superior izquierda')],
                            ['id' => 'top-right', 'name' => $this->l('Superior derecha')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'   => 'switch',
                    'label'  => $this->l('Activo'),
                    'name'   => 'active',
                    'values' => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Sí')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
                // Selector de productos asignados
                [
                    'type'    => 'textarea',
                    'label'   => $this->l('IDs de productos asignados'),
                    'name'    => 'product_ids',
                    'hint'    => $this->l('Introduce los IDs de producto separados por comas. Ej: 1,2,15'),
                    'col'     => 6,
                ],
            ],
            'submit' => [
                'title' => $this->l('Guardar'),
            ],
        ];

        // Si estamos editando, precargamos los productos asignados
        if (isset($this->object->id) && $this->object->id) {
            $productIds = ProductBadge::getProductIds((int) $this->object->id);
            $this->fields_value['product_ids'] = implode(',', $productIds);
        } else {
            $this->fields_value['product_ids'] = '';
        }

        return parent::renderForm();
    }

    /**
     * Validación y guardado del formulario (creación y edición)
     * Se llama automáticamente al hacer submit
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddproductbadges') || Tools::isSubmit('submitAddproductbadgesAndStay')) {

            // Posición
            $position = Tools::getValue('position');
            if (!in_array($position, self::VALID_POSITIONS, true)) {
                $this->errors[] = $this->l('Posición no válida. Usa top-left o top-right.');
                return;
            }

            // Colores
            $bgColor   = Tools::getValue('bg_color');
            $textColor = Tools::getValue('text_color');
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $bgColor)) {
                $this->errors[] = $this->l('El color de fondo no tiene formato válido (#rrggbb).');
                return;
            }
            if (!preg_match('/^#[0-9a-fA-F]{6}$/', $textColor)) {
                $this->errors[] = $this->l('El color del texto no tiene formato válido (#rrggbb).');
                return;
            }

            // Labels multilenguaje
            $languages = Language::getLanguages(false);
            $idLangDefault = (int) Configuration::get('PS_LANG_DEFAULT');
            foreach ($languages as $lang) {
                $labelVal = Tools::getValue('label_' . (int) $lang['id_lang']);
                if ((int) $lang['id_lang'] === $idLangDefault && empty(trim($labelVal))) {
                    $this->errors[] = $this->l('El texto de la etiqueta es obligatorio en el idioma por defecto.');
                    return;
                }
                if (mb_strlen($labelVal) > 60) {
                    $this->errors[] = sprintf(
                        $this->l('El texto de la etiqueta supera los 60 caracteres en el idioma %s.'),
                        $lang['name']
                    );
                    return;
                }
            }


            $result = parent::postProcess();

            // Guardar asignación de productos
            if ($this->object && $this->object->id) {
                $idBadge = (int) $this->object->id;
                $idShop = (int) $this->context->shop->id;

                Db::getInstance()->delete(
                    'productbadges_shop',
                    '`id_badge` = ' . $idBadge . ' AND `id_shop` = ' . $idShop
                );

                Db::getInstance()->insert('productbadges_shop', [
                    'id_badge' => $idBadge,
                    'id_shop' => $idShop,
                ]);

                $rawIds = Tools::getValue('product_ids', '');
                $productIds = $this->parseProductIds($rawIds);
                ProductBadge::saveProductAssignments($idBadge, $productIds);
            }

            return $result;
        }

        return parent::postProcess();
    }

    /**
     * Parsea y sanitiza la lista de IDs de producto enviada desde el formulario
     *
     * @param string $raw
     * @return array
     */
    private function parseProductIds($raw)
    {
        $parts = explode(',', $raw);
        $ids   = [];
        foreach ($parts as $part) {
            $id = (int) trim($part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_unique($ids);
    }
}