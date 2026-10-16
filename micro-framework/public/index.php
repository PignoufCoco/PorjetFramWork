<?php

use App\Application;

require_once __DIR__ . '/../vendor/autoload.php';

// Application initialise le conteneur et le routeur.
$app = new Application(dirname(__DIR__));
$router = $app->router();

require __DIR__ . '/../config/routes.php';

// Reception de la requete, routage et envoi de la reponse.
$app->run();
