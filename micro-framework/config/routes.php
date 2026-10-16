<?php
use App\Response;
use App\Request;
use Controllers\ArticleController;
/** @var App\Router $router */
// A décommenter pour utiliser les controllers. Commenter les routes correspondant au dessus.
$router->get('/articles',      [ArticleController::class, 'index']);
$router->get('/articles/search', [ArticleController::class, 'search']);
$router->get('/articles/show', [ArticleController::class, 'show']);
$router->get('/articles/show/{id}', [ArticleController::class, 'show_data']);
$router->get('/articles/add',     [ArticleController::class, 'create']);
$router->post('/articles/add',     [ArticleController::class, 'add']);
$router->get('/', [Controllers\HomeController::class, 'index']);
