<?php
/**
 * Clase ProductBadge — ObjectModel de PrestaShop
 * Representa una etiqueta visual (badge) asignable a productos
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductBadge extends ObjectModel
{
    /** @var string Color de fondo */
    public $bg_color;

    /** @var string Color del texto */
    public $text_color;

    /** @var string Posición */
    public $position;

    /** @var int Estado  */
    public $active;

    /** @var string Texto de la etiqueta (multilenguaje) */
    public $label;

    /** @var string Fecha de creación */
    public $date_add;

    /** @var string Fecha de última modificación */
    public $date_upd;

    /**
     * Definición del ObjectModel para PrestaShop
     */
    public static $definition = [
        'table'     => 'productbadges',
        'primary'   => 'id_badge',
        'multilang' => true,
        'multishop' => true,
        'fields'    => [
            'bg_color'   => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isColor',
                'size'     => 7,
                'required' => true,
            ],
            'text_color' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isColor',
                'size'     => 7,
                'required' => true,
            ],
            'position'   => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 10,
                'required' => true,
            ],
            'active'     => [
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'date_add'   => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd'   => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],

            'label'      => [
                'type'     => self::TYPE_STRING,
                'lang'     => true,
                'validate' => 'isGenericName',
                'size'     => 60,
                'required' => true,
            ],
        ],
    ];

    /**
     * Devuelve todas las badges activas asignadas a un producto,
     * en el idioma indicado, limitadas al máximo configurado.
     *
     * @param int $idProduct  ID del producto
     * @param int $idLang     ID del idioma activo
     * @param int $idShop     ID de la tienda activa
     * @param int $maxBadges  Número máximo de badges a mostrar
     *
     * @return array
     */
    public static function getByProduct($idProduct, $idLang, $idShop, $maxBadges = 3)
    {
        $idProduct = (int) $idProduct;
        $idLang    = (int) $idLang;
        $idShop    = (int) $idShop;

        // Clamp entre 1 y 10 para evitar LIMIT 0, negativos o valores excesivos
        $maxBadges = max(1, min(10, (int) $maxBadges));

        return Db::getInstance()->executeS(
            'SELECT b.`id_badge`, b.`bg_color`, b.`text_color`, b.`position`, bl.`label`
            FROM `' . _DB_PREFIX_ . 'productbadges` b
            INNER JOIN `' . _DB_PREFIX_ . 'productbadges_lang` bl
                ON (b.`id_badge` = bl.`id_badge` AND bl.`id_lang` = ' . $idLang . ')
            INNER JOIN `' . _DB_PREFIX_ . 'productbadges_product` bp
                ON (b.`id_badge` = bp.`id_badge` AND bp.`id_product` = ' . $idProduct . ')
            INNER JOIN `' . _DB_PREFIX_ . 'productbadges_shop` bs
                ON (b.`id_badge` = bs.`id_badge` AND bs.`id_shop` = ' . $idShop . ')
            WHERE b.`active` = 1
            ORDER BY b.`id_badge` ASC
            LIMIT ' . $maxBadges
        );
    }

    /**
     * Devuelve los IDs de productos asignados a una badge
     *
     * @param int $idBadge
     * @return array Lista de id_product
     */
    public static function getProductIds($idBadge)
    {
        $idBadge = (int) $idBadge;

        $rows = Db::getInstance()->executeS(
            'SELECT `id_product`
            FROM `' . _DB_PREFIX_ . 'productbadges_product`
            WHERE `id_badge` = ' . $idBadge
        );

        return array_column($rows, 'id_product');
    }

    /**
     * Guarda la relación badge - productos (muchos a muchos)
     * Primero borra los existentes y luego inserta los nuevos
     *
     * @param int   $idBadge
     * @param array $productIds  Array de IDs de productos
     *
     * @return bool
     */
    public static function saveProductAssignments($idBadge, array $productIds)
    {
        $idBadge = (int) $idBadge;

        // Borrar asignaciones previas de esta badge
        Db::getInstance()->delete(
            'productbadges_product',
            '`id_badge` = ' . $idBadge
        );

        if (empty($productIds)) {
            return true;
        }

        $rows = [];
        foreach ($productIds as $idProduct) {
            $idProduct = (int) $idProduct;
            if ($idProduct > 0) {
                $rows[] = [
                    'id_badge'   => $idBadge,
                    'id_product' => $idProduct,
                ];
            }
        }

        if (empty($rows)) {
            return true;
        }

        return Db::getInstance()->insert('productbadges_product', $rows);
    }
}
