<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

require_once __DIR__ . '/../../../../wp-load.php';
include_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once __DIR__ . '/../vendor/autoload.php';

class DynamicoreController
{
    private $response;
    private $data;
    private $retro;

    /**
     * DynamicoreController constructor
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     *
     */
    public function save()
    {
        try {
            $this->__init__();
            $this->response = [
                'error' => false,
                'message' => 'Las configuraciones fueron guardadas correctamente.',
                'retro' => $this->retro,
            ];
        } catch (\Exception $e) {
            error_log($e->getMessage());

            $this->response = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        return json_encode($this->response);
    }

    /**
     * Save all plugin configuration
     * @throws Exception
     */
    private function __init__()
    {
        # Active payment method
    }
}

function dc_api_init()
{
    $config = new DynamicoreController($_POST);

    exit($config->save());
}

add_action('rest_api_init', function () {
    register_rest_route(
        'dynamicore/',
        'config',
        [
            'methods' => 'POST',
            'callback' => 'dc_api_init',
        ]
    );
});
