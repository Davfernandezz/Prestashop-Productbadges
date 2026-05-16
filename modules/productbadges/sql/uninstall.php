<?php
/**
 * Elimina todas las tablas del módulo productbadges
 * Se ejecuta durante la desinstalación del módulo
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];

// Primero tablas relacionadas y después la tabla principal.
// Este orden evita problemas si en el futuro se añaden claves foráneas.
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_product`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_shop`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'productbadges`;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;
