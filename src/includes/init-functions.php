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

function dynamicore_after_product_price()
{
    $dynamicore_plugin_name = 'dynamicore';
    $showAfterProce = get_option(
        "{$dynamicore_plugin_name}_show_after_price",
        false
    );

    if ($showAfterProce) {
        $dynamicore_panel = 'https://admin.dynamicore.io';
        $client = new GuzzleHttp\Client([
            # Base URI is used with relative requests
            'base_uri' => 'https://connector.dynamicore.io',
            # You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET', "/kyc/a071db79d3d74ea9a56c0e754a69a330/check");
        ['data' => ['company' => $company]] = json_decode($response->getBody(), true);

        $product = wc_get_product(get_the_ID());

        $context = [
            'company' => $company,
            'dynamicore_panel' => $dynamicore_panel,
            'external_route' => '/public/integrations/enpagos?' . http_build_query([
                'costo_de_producto' => $product->get_price(),
                'giro_del_negocio' => get_option(
                    "{$dynamicore_plugin_name}_giro_del_negocio",
                    ''
                ),
                'nombre_de_la_tienda' => get_option(
                    "{$dynamicore_plugin_name}_nombre_de_la_tienda",
                    ''
                ),
                'producto' => get_the_title(get_the_ID()),
            ]),
            'site_url' => get_site_url(),
            'shop_url' => wc_get_page_permalink('store'),
        ];

        # CSS
        wp_register_style(
            'highslide',
            plugins_url('../lib/highslide/css/highslide.css', __FILE__)
        );
        wp_enqueue_style('highslide');

        # JS
        wp_register_script(
            'highslide',
            plugins_url('../lib/highslide/js/highslide-with-html.min.js', __FILE__)
        );
        wp_register_script(
            'dynamicore_product',
            plugins_url('../templates/js/dynamicore_product.js', __FILE__)
        );
        wp_enqueue_script('highslide');
        wp_enqueue_script('dynamicore_product');

        $template = new TemplateController();
        echo $template->render('after_product_price.twig', $context);
    }
}
