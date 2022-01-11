<?php

/**
 * Plugin Name: Enpagos
 * Plugin URI: https://enpagos.mx/
 * Description: Accept payments with Enpagos
 * Author: <a href="https://enpagos.mx/" target="_blank">Enpagos</a>
 * Author URI: https://enpagos.mx/
 * Version: 1.0.0
 * Licence: MIT
 * Text Domain: dynamicore
 * Domain Path: /languages
 * Requires at least: 5.3
 * Requires PHP: 7.1
 */

require_once __DIR__ . '/controllers/DynamicoreController.php';
require_once __DIR__ . '/controllers/TemplateController.php';
include_once __DIR__ . '/includes/config-functions.php';
include_once __DIR__ . '/includes/init-functions.php';
include_once __DIR__ . '/includes/gateways/dynamicore-receipt.php';
include_once __DIR__ . '/includes/gateways/dynamicore-webhook.php';

/**
 * Activación de la nueva página de configuración
 */
add_action('admin_menu', 'dynamicore_add_admin_page');

/**
 * Registro de rutina de activación
 */
register_activation_hook(__FILE__, function () {
    return;
});

/**
 * Main function registration for payment gateways
 */
add_action('plugins_loaded', 'dynamicore_init', 0);
