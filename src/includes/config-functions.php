<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

require_once __DIR__ . '/../vendor/guzzlehttp/guzzle/src/Client.php';

function dynamicore_update_webhook(string $url): bool
{
    return true;
}

function dynamicore_active_payment(bool $active = false): void
{
    update_option('woocommerce_dynamicore_payments_settings', [
        'enabled' => $active ? 'yes' : 'no',
    ]);
}

function dynamicore_update_options($data): bool
{
    foreach ($data as $key => $value) {
        delete_option($key);
        update_option($key, $value);
    }

    dynamicore_update_webhook('');

    dynamicore_active_payment(true);

    return true;
}

function dynamicore_config_page()
{
    $dynamicore_noticies = [];
    $dynamicore_plugin_name = 'dynamicore';
    $people_form_key = get_option(
        "{$dynamicore_plugin_name}_people_form_key",
        ''
    );

    if ($_POST) {
        $updated = dynamicore_update_options($_POST);

        $dynamicore_noticies[] = [
            'level' => $updated ? 'success' : 'danger',
            'text' => $updated
                ? __('Plugin settings were saved correctly', $dynamicore_plugin_name)
                : __('An error occurred while saving plugin settings', $dynamicore_plugin_name),
        ];
    }

    $client = new GuzzleHttp\Client([
        # Base URI is used with relative requests
        'base_uri' => 'https://connector.dynamicore.io',
        # You can set any number of default request options.
        'timeout'  => 2.0,
    ]);

    $response = $client->request('GET', "/kyc/a071db79d3d74ea9a56c0e754a69a330/check");

    ['data' => [
        'company' => $company,
        'fields' => $fields,
        'tabs' => $tabs,
    ]] = json_decode($response->getBody(), true);

    $site_url = get_site_url();
    $context = [
        'company' => $company,
        'site_url' => $site_url,
        'plugin_name' => $dynamicore_plugin_name,
        'button_save' => [
            'label' => __('Save settings', $dynamicore_plugin_name),
        ],
        'noticies' => $dynamicore_noticies,
        'inputs' => [
            [
                'group' => 'general',
                'label' => __('Nombre a mostrar', $dynamicore_plugin_name),
                'name' => 'gateway_title',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_gateway_title",
                    'Enpagos'
                ),
            ],
            [
                'group' => 'general',
                'label' => __('Habilitado', $dynamicore_plugin_name),
                'name' => 'gateway_enabled',
                'type' => 'select',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_gateway_enabled",
                    0
                ),
                'options' => [
                    ['label' => __('No', $dynamicore_plugin_name), 'value' => 0],
                    ['label' => __('Si', $dynamicore_plugin_name), 'value' => 1],
                ],
            ],
            # ============================================================================
            [
                'group' => 'general',
                'label' => __('Giro del negocio', $dynamicore_plugin_name),
                'name' => 'giro_del_negocio',
                'type' => 'text',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_giro_del_negocio",
                    ''
                ),
            ],
            [
                'group' => 'general',
                'label' => __('Nombre del distribuidor o tienda', $dynamicore_plugin_name),
                'name' => 'nombre_de_la_tienda',
                'type' => 'text',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_nombre_de_la_tienda",
                    ''
                ),
            ],
            # ============================================================================
            // [
            //     'group' => 'template_form',
            //     'label' => __('Public form key', $dynamicore_plugin_name),
            //     'name' => 'people_form_key',
            //     'value' => $people_form_key,
            // ],
            [
                'group' => 'orders',
                'label' => __('Reducción de inventario', $dynamicore_plugin_name),
                'name' => 'inventory_reduction',
                'type' => 'select',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_inventory_reduction",
                    'init'
                ),
                'options' => [
                    ['label' => 'Al crear la orden', 'value' => 'init'],
                    ['label' => 'Al confirmar el pago', 'value' => 'fin'],
                    ['label' => 'Nunca', 'value' => 'no'],
                ],
            ],
            [
                'group' => 'orders',
                'label' => __('Estado inicial de la orden', $dynamicore_plugin_name),
                'name' => 'initial_status',
                'type' => 'select',
                'value' => get_option(
                    "{$dynamicore_plugin_name}_initial_status",
                    'pending'
                ),
                'options' => [
                    ['label' => 'Pendiente de pago', 'value' => 'pending'],
                    ['label' => 'En espera', 'value' => 'on-hold'],
                ],
            ],
            [
                'group' => 'webhook',
                'label' => __('URL', $dynamicore_plugin_name),
                'name' => 'webhook',
                'type' => 'textarea',
                'readonly' => true,
                'value' => get_option(
                    "{$dynamicore_plugin_name}_webhook",
                    $site_url . '/wp-json/dynamicore/webhook'
                ),
            ],
        ],
        'tabs' => [],
    ];

    array_push($context['tabs'], [
        'name' => 'general',
        'label' => __('Pasarela de pago', $dynamicore_plugin_name),
        'groups' => [
            [
                'label' => __('General', $dynamicore_plugin_name),
                'icon' => 'admin-generic',
                'name' => 'general',
            ],
            // [
            //     'description' => [
            //         'You can view this information on the ',
            //         '<b><a target="_BLANK" href="',
            //         'https://admin.dynamicore.io/settings/templates/people',
            //         '">Dynamicore dashboard</a></b>.',
            //     ],
            //     'icon' => 'post-status',
            //     'label' => __('Template form', $dynamicore_plugin_name),
            //     'name' => 'template_form',
            // ],
        ],
    ]);

    $fieldGroups = [
        // [
        //     'description' => [
        //         'Map the Woocommerce order information with ',
        //         'the equivalent fields in the Dynamicore form'
        //     ],
        //     'icon' => 'embed-generic',
        //     'name' => 'dynamicore_people_form',
        //     'label' => __('Field mapping', $dynamicore_plugin_name),
        // ],
    ];

    if ($tabs)
    {
        foreach($tabs as $tabGroup) foreach($tabGroup['groups'] as $groupField)
        {
            array_push($fieldGroups, [
                'name' => "dynamicore_people_form_{$groupField['value']}",
                'label' => $groupField['name'],
            ]);
        }
    }

    // array_push($context['tabs'], [
    //     'name' => 'dynamicore_people_form',
    //     'label' => __('Field mapping', $dynamicore_plugin_name),
    //     'groups' => $fieldGroups,
    // ]);

    array_push(
        $context['tabs'],
        [
            'name' => 'orders',
            'label' => __('Ordenes', $dynamicore_plugin_name),
            'groups' => [
                [
                    'icon' => 'cart',
                    'label' => __('Ordenes', $dynamicore_plugin_name),
                    'name' => 'orders',
                ],
            ],
        ],
        [
            'name' => 'notifications',
            'label' => __('Notificaciones', $dynamicore_plugin_name),
            'groups' => [
                [
                    'description' => [
                        'Receive notification when a payment has been made. ',
                        'You can check the active Webhook ',
                        '<b><a target="_BLANK" href="',
                        'https://admin.dynamicore.io/settings/webhook',
                        '">here</a></b>.',
                    ],
                    'icon' => 'admin-links',
                    'label' => __('Webhook', $dynamicore_plugin_name),
                    'name' => 'webhook',
                ],
            ],
        ],
    );

    // if ($fields)
    // {
    //     foreach ($fields as $field) {
    //         array_push($context['inputs'], [
    //             'group' => "dynamicore_people_form_{$field['group']}",
    //             'label' => $field['displayname'],
    //             'name' => "field[{$field['fieldname']}]",
    //             'type' => 'select',
    //             'value' => '',
    //             'options' => [
    //                 ['label' => 'Select an option'],
    //                 ['label' => 'Customer->Name', 'value' => 'get_billing_first_name'],
    //                 ['label' => 'Customer->LastName', 'value' => 'get_billing_last_name'],
    //                 ['label' => 'Customer->Email', 'value' => 'get_billing_email'],
    //                 ['label' => 'Customer->Phone', 'value' => 'get_billing_phone'],
    //                 ['label' => 'Customer->Address 1', 'value' => 'get_billing_address_1'],
    //                 ['label' => 'Customer->Address 2', 'value' => 'get_billing_address_2'],
    //                 ['label' => 'Customer->PostalCode', 'value' => 'get_billing_postcode'],
    //                 ['label' => 'Customer->City', 'value' => 'get_billing_city'],
    //                 ['label' => 'Customer->State', 'value' => 'get_billing_state'],
    //                 ['label' => 'Customer->Country', 'value' => 'get_billing_country'],
    //                 ['label' => 'Order->Total', 'value' => 'get_total'],
    //             ],
    //         ]);
    //     }
    // }

    wp_register_script(
        'jquery_admin',
        'https://code.jquery.com/jquery-3.6.0.slim.min.js'
    );
    wp_register_script(
        'dynamicore_admin',
        plugins_url('../templates/js/dynamicore_admin.js', __FILE__)
    );
    wp_register_style(
        'dynamicore_admin',
        plugins_url('../templates/css/dynamicore_admin.css', __FILE__)
    );

    wp_enqueue_script('jquery_admin');
    wp_enqueue_script('dynamicore_admin');
    wp_enqueue_style('dynamicore_admin');

    $template = new TemplateController();
    echo $template->render('configuration.twig', $context);
}

function dynamicore_add_admin_page()
{
    $page_title = __('Enpagos - Configuration', $dynamicore_plugin_name);
    $menu_title = __('Enpagos', $dynamicore_plugin_name);
    $capanility = 'manage_options';
    $menu_slug = 'enpagos-settings';
    $function = 'dynamicore_config_page';
    $icon_url = plugins_url('../templates/img/logo.png', __FILE__);
    $position = 110;

    add_menu_page(
        $page_title,
        $menu_title,
        $capanility,
        $menu_slug,
        $function,
        $icon_url,
        $position
    );
}
