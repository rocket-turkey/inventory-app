<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Database.php';
require __DIR__ . '/../app/InputValidator.php';
require __DIR__ . '/../app/ProductRepository.php';
require __DIR__ . '/../app/StockMovementRepository.php';
require __DIR__ . '/../app/InventoryService.php';
require __DIR__ . '/../app/InventoryController.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

session_start();
$request = Request::createFromGlobals();
$connection = Database::connect();
$products = new ProductRepository($connection);
$movements = new StockMovementRepository($connection);
$controller = new InventoryController(
    $products,
    $movements,
    new InventoryService($connection, $products, $movements),
    new InputValidator(),
    new CsrfTokenManager(),
    new Environment(new FilesystemLoader(__DIR__ . '/../templates'), ['autoescape' => 'html'])
);

$route = $request->getMethod() . ' ' . $request->getPathInfo();
$response = match ($route) {
    'GET /' => $controller->index($request),
    'POST /products' => $controller->saveProduct($request),
    'POST /stock' => $controller->adjustStock($request),
    default => new Response('Page not found.', 404),
};
$response->send();
