<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

function dynamicore_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // load_plugin_textdomain(
    //     'dynamicore',
    //     false,
    //     dirname(plugin_basename(__FILE__)) . '/languages'
    // );

    # Gateways
    include_once __DIR__ . '/gateways/wc-gateway-dynamicore.php';
}
