<?php

declare(strict_types=1);

use MapeiaCEP\HomeController;
use MapeiaCEP\Http\CurlHttpClient;
use MapeiaCEP\View;
use MapeiaCEP\ViaCepClient;

require dirname(__DIR__) . '/vendor/autoload.php';

// Nunca exibir erros internos ao usuário; registrar em log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

$view = new View(dirname(__DIR__) . '/templates');
$controller = new HomeController(new ViaCepClient(new CurlHttpClient()));

try {
    echo $view->renderizar('home', ['titulo' => 'MapeiaCEP'] + $controller->tratar($_GET));
} catch (\Throwable $e) {
    error_log('[MapeiaCEP] ' . $e);
    http_response_code(500);
    echo 'Ocorreu um erro inesperado. Tente novamente mais tarde.';
}
