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
    $product = wc_get_product(get_the_ID());
    $terms = get_the_terms(get_the_ID(), 'product_cat');

    $showAfterPrice = get_option(
        "{$dynamicore_plugin_name}_show_after_price",
        '0'
    ) === '1';

    if ($showAfterPrice) {
        $whiteList = explode(',', get_option(
            'dynamicore_allow_categories',
            ''
        ));

        foreach ($whiteList as $key => $val) {
            $newVal = strtoupper(trim($val));

            if ($newVal === '') {
                unset($whiteList[$key]);
            } else {
                $whiteList[$key] = $newVal;
            }
        }

        if ($whiteList) {
            foreach ($terms as $term) {
                if (!in_array(strtoupper($term->name), $whiteList)) {
                    $showAfterPrice = false;
                    break;
                }
            }
        }
    }

    if ($showAfterPrice) {
        $client = new GuzzleHttp\Client([
            # Base URI is used with relative requests
            'base_uri' => 'https://connector.dynamicore.io',
            # You can set any number of default request options.
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET', "/kyc/a071db79d3d74ea9a56c0e754a69a330/check");
        ['data' => ['company' => $company]] = json_decode($response->getBody(), true);

        $context = [
            'company' => $company,
            'dynamicore_panel' => str_starts_with($_SERVER['HTTP_HOST'], 'localhost')
                ? 'http://localhost:3000'
                : 'https://admin.dynamicore.io',
            'external_route' => '/public/integrations/enpagos?' . http_build_query([
                'costo_de_producto' => $product->get_price(),
                'nombre_de_asesor_de_ventas' => get_option(
                    "{$dynamicore_plugin_name}_nombre_de_asesor_de_ventas",
                    ''
                ),
                'email_de_vendedor' => get_option(
                    "{$dynamicore_plugin_name}_email_de_vendedor",
                    ''
                ),
                'nombre_de_la_tienda' => get_option(
                    "{$dynamicore_plugin_name}_nombre_de_la_tienda",
                    ''
                ),
                'producto' => get_the_title(get_the_ID()),
                'numero_de_equipos' => 1,
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
