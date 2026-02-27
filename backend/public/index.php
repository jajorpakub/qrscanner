<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

// Load env variables
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = AppFactory::create();

// Middleware
$app->addErrorMiddleware(getenv('APP_DEBUG') === 'true', true, true);

// CORS Middleware
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// API Routes
$app->group('/api', function ($group) {
    // Auth routes
    $group->post('/auth/register', \App\Controllers\AuthController::class . ':register');
    $group->post('/auth/login', \App\Controllers\AuthController::class . ':login');
    
    // Device routes (public read)
    $group->get('/devices/{id}', \App\Controllers\DeviceController::class . ':show');
    $group->get('/devices/{id}/full', \App\Controllers\DeviceController::class . ':showFull');
    
    // Protected routes
    $group->group('', function ($protectedGroup) {
        $protectedGroup->post('/devices', \App\Controllers\DeviceController::class . ':create');
        $protectedGroup->put('/devices/{id}', \App\Controllers\DeviceController::class . ':update');
        $protectedGroup->delete('/devices/{id}', \App\Controllers\DeviceController::class . ':delete');
        $protectedGroup->get('/devices', \App\Controllers\DeviceController::class . ':list');
        
        // QR Code routes
        $protectedGroup->post('/devices/{id}/generate-qr', \App\Controllers\QRController::class . ':generate');
        
        // Technical records
        $protectedGroup->post('/devices/{id}/records', \App\Controllers\RecordController::class . ':create');
        $protectedGroup->get('/devices/{id}/records', \App\Controllers\RecordController::class . ':list');
    })->add(\App\Middleware\AuthMiddleware::class);
});

// Health check
$app->get('/health', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
