<?php
/**
 * Tablas necesarias para el módulo productbadges
 * Se ejecuta durante la instalación del módulo
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];

// Tabla principal de badges
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges` (
    `id_badge` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bg_color` VARCHAR(7) NOT NULL DEFAULT \'#000000\',
    `text_color` VARCHAR(7) NOT NULL DEFAULT \'#ffffff\',
    `position` ENUM(\'top-left\', \'top-right\') NOT NULL DEFAULT \'top-left\',
    `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY (`id_badge`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Tabla de textos traducibles por idioma
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_lang` (
    `id_badge` INT(10) UNSIGNED NOT NULL,
    `id_lang` INT(10) UNSIGNED NOT NULL,
    `label` VARCHAR(60) NOT NULL DEFAULT \'\',
    PRIMARY KEY (`id_badge`, `id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Tabla de relación muchos a muchos producto - badge
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_product` (
    `id_badge` INT(10) UNSIGNED NOT NULL,
    `id_product` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_badge`, `id_product`),
    KEY `idx_productbadges_product_id_product` (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

// Tabla multitienda
// Índice por id_shop para filtrar badges activas
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'productbadges_shop` (
    `id_badge` INT(10) UNSIGNED NOT NULL,
    `id_shop` INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (`id_badge`, `id_shop`),
    KEY `idx_productbadges_shop_id_shop` (`id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;
