<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

require_once __DIR__ . '/../../vendor/guzzlehttp/guzzle/src/Client.php';

class WC_Gateway_Dynamicore extends WC_Payment_Gateway
{
    const VERSION = '1.0.0';
    const GATEWAY_ID = 'dynamicore_payments';

    private $dynamicore_people_form_key;
    private $debug;

    public function __construct()
    {
        global $woocommerce;

        $this->id = self::GATEWAY_ID;
        $this->has_fields = true;
        $this->method_title = 'Dynamicore Payments';
        $this->method_description = __('<p>Accept payments with Dynamicore</p>', 'dynamicore');
        $this->has_fields = true;
        $this->controlVision = 'no';
        $this->init_form_fields();
        $this->init_settings();

        $this->activeplugin = true;
        $this->debug = false;
        $this->title = get_option('dynamicore_gateway_title');
        $this->dynamicore_people_form_key = 'a071db79d3d74ea9a56c0e754a69a330'; # get_option('dynamicore_people_form_key', false);
        $this->inventory_reduction = get_option('dynamicore_inventory_reduction');
        $this->initial_status = get_option('dynamicore_initial_status');

        // Logs
        if ('yes' == $this->debug) {
            if (floatval($woocommerce->version) >= 2.1) {
                if (class_exists('WC_Logger')) {
                    $this->log = new WC_Logger();
                } else {
                    $this->log = WC()->logger();
                }
            } else {
                $this->log = $woocommerce->logger();
            }
        }

        $this->setDynamicoreConfig();

        // Just validate on admin site
        if (is_admin()) {
            $this->title = "Dynamicore payments";
        }

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [$this, 'process_admin_options']
        );
    }

    public function init_form_fields()
    {
        $page = get_site_url() . '/wp-admin/admin.php?page=dynamicore-config';

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'bpagos'),
                'label' => __('Enable Dynamicore', 'bpagos'),
                'type' => 'checkbox',
                'description' => __("Wordpress desde <a href=\"{$page}\">AQUÍ</a>", 'bpagos'),
                'default' => 'no'
            )
        );
    }

    private function setDynamicoreConfig()
    {
        global $wp_version;
        global $woocommerce;
    }

    public function process_payment($order_id)
    {
        global $woocommerce;

        try {
            $order = new WC_Order($order_id);

            $client = new GuzzleHttp\Client([
                # Base URI is used with relative requests
                'base_uri' => 'https://connector.dynamicore.io',
                # You can set any number of default request options.
                'timeout' => 2.0,
            ]);

            $pii = $this->paymentData;
            $pii['costo_de_producto'] = $order->get_total();
            $pii['numero_de_equipos'] = $order->get_item_count();
            $pii['producto'] = 'Order #' . $order_id;
            $pii['giro_del_negocio'] = get_option(
                "dynamicore_giro_del_negocio",
                ''
            );
            $pii['nombre_de_la_tienda'] = get_option(
                "dynamicore_nombre_de_la_tienda",
                ''
            );
            $pii['primer_nombre'] = $order->get_billing_first_name();
            $pii['primer_apellido'] = $order->get_billing_last_name();;
            $pii['telefono'] = $order->get_billing_phone();
            $pii['correo_electronico'] = $order->get_billing_email();

            $client->request(
                'POST',
                "/kyc/{$this->dynamicore_people_form_key}/check",
                [
                    'body' => json_encode([
                        'client_type' => $this->dynamicore_people_form_key,
                        'pii' => $pii,
                    ]),
                ]
            );

            wc_add_notice(
                __('Su orden de pago en Dynamicore está lista.', 'dynamicore'),
                'success'
            );
        } catch (Exception $e) {
            $errors = [
                'Enpagos: Request Error [409]: ',
                'Request Error [200]: ',
                'Request Error [400]: '
            ];
            $message = json_decode(str_replace($errors, '', $e->getMessage()), true);
            $message = isset($message['message'])
                ? $message['message']
                : $e->getMessage();

            wc_add_notice(
                __('Enpagos error place order:<br/>' . $message, 'dynamicore'),
                'error'
            );

            return false;
        }

        $order->update_status(
            $this->initialstate,
            __('Enpagos - Pending', 'dynamicore')
        );

        if ($this->completeorder == 'init') {
            $order->reduce_order_stock();
        }

        $woocommerce->cart->empty_cart();

        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($order)
        ];
    }

    public function payment_fields()
    {
        global $woocommerce;

        try {
            if (!$this->is_valid_for_use()) {
                echo (__(
                    'No disponilbe para este producto o productos.',
                    'dynamicore'
                ));
                return;
            }

            $cart_total = $woocommerce->cart->total;
            $admin_procedures = $woocommerce->cart->get_cart_contents_count();

            wp_register_script(
                'dynamicore_store',
                plugins_url('../../templates/js/dynamicore_store.js', __FILE__)
            );
            wp_register_style(
                'dynamicore_store',
                plugins_url('../../templates/css/dynamicore_store.css', __FILE__)
            );
            wp_enqueue_script('dynamicore_store');
            wp_enqueue_style('dynamicore_store');

            $client = new GuzzleHttp\Client([
                # Base URI is used with relative requests
                'base_uri' => 'https://connector.dynamicore.io',
                # You can set any number of default request options.
                'timeout' => 2.0,
            ]);

            $response = $client->request('GET', "/kyc/{$this->dynamicore_people_form_key}/check");

            ['data' => $context] = json_decode($response->getBody(), true);

            $fields = [];
            $availableFields = [
                'periodos',
                'promedio_de_ventas_semanales',
                'creditos_actuales',
            ];
            foreach ($context['fields'] as $field) {
                if (!in_array($field['fieldname'], $availableFields)) {
                    continue;
                }

                $fieldOptions = [];

                if ($field['fieldname'] === 'periodos') {
                    if ($cart_total <= 200000) {
                        $interest = $cart_total * (0.23 / 360 * 7);
                        $interest_iva = $interest * 0.16;
                        $life_insurance = $cart_total <= 500000
                            ? $cart_total * 0.11 / 1000 * 1.16
                            : 500000 * 0.11 / 1000 * 1.16;
                        $admin_expense = 50 * $admin_procedures;

                        foreach ([52, 78, 104] as $period) {
                            $payments = number_format($cart_total / $period + $interest + $interest_iva + $life_insurance + $admin_expense, 2);
                            array_push($fieldOptions, [
                                'description' => "Precio incluye IVA: {$cart_total}",
                                'label' => "Plazos: {$period} semanas",
                                'subtitle' => "Pagos fijos: {$payments}",
                                'value' => $period,
                            ]);
                        }
                    } else {
                        $interest = $cart_total * (0.20 / 12);
                        $life_insurance = $cart_total <= 500000
                            ? $cart_total * 0.44 / 1000 * 1.16
                            : 500000 * 0.44 / 1000 * 1.16;
                        $admin_expense = 400 * $admin_procedures;

                        foreach ([12, 24, 36, 48, 60] as $period) {
                            $payments = number_format($cart_total / $period + $interest + $life_insurance + $admin_expense, 2);
                            array_push($fieldOptions, [
                                'description' => "Precio incluye IVA: {$cart_total}",
                                'label' => "Plazos: {$period} meses",
                                'subtitle' => "Pagos fijos: {$payments}",
                                'value' => $period,
                            ]);
                        }
                    }

                    $context['paymentPeriods'] = $fieldOptions;
                } elseif (isset($field['options'])) {
                    foreach ($field['options'] as $option) {
                        array_push($fieldOptions, [
                            'label' => $option['name'],
                            'value' => $option['id'],
                        ]);
                    }
                }

                array_push($fields, [
                    'label' => $field['displayname'],
                    'group' => $field['group'],
                    'name' => $field['fieldname'],
                    'options' => $fieldOptions,
                    'type' => $field['displaytype'],
                ]);
            }

            $context['fields'] = $fields;
            $context['theme'] = [
                'colors' => [
                    'primary' => get_option('dynamicore_primary_color', '0a3aa4'),
                    'secondary' => get_option('dynamicore_secondary_color', '4868b0'),
                    'textPrimary' => get_option('dynamicore_text_primary_color', '000000'),
                    'textSecondary' => get_option('dynamicore_text_secondary_color', 'ffffff'),
                ],
            ];

            $template = new TemplateController();

            echo $template->render('payment_form.twig', $context);
        } catch (Exception $e) {
            wc_add_notice(
                __('pagos Error:', 'bpagos') . $e->getMessage(),
                'error'
            );
            $this->log->add('pagos', $e->getMessage());
        }
    }

    public function validate_fields()
    {
        $this->orderProvider = $_POST['dynamicore_provider'];

        $data = [];
        foreach ($_POST as $key => $val) {
            if (str_starts_with($key, 'dynamicore_')) {
                $data[str_replace('dynamicore_', '', $key)] = $val;
            }
        }
        $this->paymentData = $data;

        return true;
    }

    public function is_valid_for_use()
    {
        global $woocommerce;

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            $terms = get_the_terms($product_id, 'product_cat');

            $whiteList = explode(',', get_option(
                "dynamicore_allow_categories",
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
                        return false;
                    }
                }
            }
        }

        try {
            $cart_total = $woocommerce->cart->total;

            return $cart_total > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}

function dynamicore_register_payment_method($methods)
{
    $methods[] = 'WC_Gateway_Dynamicore';

    return $methods;
}

add_filter(
    'woocommerce_payment_gateways',
    'dynamicore_register_payment_method'
);
