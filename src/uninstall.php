<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

$plugin_name = 'dynamicore';
$plugin_settings = [
    "woocommerce_{$plugin_name}_payment_settings",
    "{$plugin_name}_keys_client",
    "{$plugin_name}_keys_secret",
    "{$plugin_name}_webhook",
    "{$plugin_name}_payment_title",
    "{$plugin_name}_payment_enabled",
    "{$plugin_name}_inventory_reduction",
    "{$plugin_name}_initial_status",
];

if (defined('WP_UNINSTALL_PLUGIN')) {
    foreach ($plugin_settings as $key) {
        delete_option($key);
    }
}
