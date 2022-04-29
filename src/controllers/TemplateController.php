<?php

/**
 * @author José Beltrán Solís <j.beltran@live.com.mx>
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateController
{
    private $twig;

    /**
     * TempateController constructor
     * @param array $data
     */
    public function __construct()
    {
        $loader = new FilesystemLoader(__DIR__ . '/../templates');
        $options = [];

        $this->twig = new Environment($loader, $options);
    }

    /**
     *
     */
    public function render(string $template, array $context = []): string
    {
        $wrapper = $this->twig->load($template);

        return $wrapper->render($context);
    }
}
