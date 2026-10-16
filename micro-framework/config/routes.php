<?php
// config/routes.php
// Ce fichier déclare toutes les routes de l'application.
// C'est l'équivalent de config/routes.yaml chez Symfony,
// ou des attributs #[Route('/about')] sur les contrôleurs.
// Ici on utilise du PHP pur, pas de YAML, pas d'annotations.
//
// La variable $router est créée dans public/index.php
// avant que ce fichier soit inclus via require.
// C'est pour ça qu'on peut l'utiliser directement ici
// sans la déclarer, elle vient du scope parent.

use App\Response;
use App\Request;

use Controllers\ArticleController;



// A décommenter pour le Router sans paramètres (et commenter Router)

///** @var App\RouterSimple $router */

// A décommenter pour le Router avec paramètres (et commenter RouterSimple)

/** @var App\Router $router */


// ── Route 1 : page d'accueil ─────────────────────────────────
// Quand un visiteur fait GET / (la racine du site),
// exécuter cette closure et retourner du HTML.
// La closure reçoit la Request en paramètre (même si on ne
// l'utilise pas ici).
// $router->get('/', function (App\Request $request) {
//     return App\Response::html('<h1>Bienvenue sur le micro-framework !</h1>');
// });

// ── Route 2 : page à propos ─────────────────────────────────
// Même principe: GET /about retourne une page statique.
// $router->get('/about', function (App\Request $request) {
//     return App\Response::html('<h1>À propos</h1><p>Un micro-framework pédagogique.</p>');
// });

// ── Route 3 : traitement du formulaire de contact ────────────
// POST /contact: reçoit les données d'un formulaire.
// Ici on utilise la Request : $request->input('name') lit
// la valeur du champ "name" envoyé dans le corps de la requête
// ($_POST['name'] en PHP classique).
// Le deuxième argument 'Anonyme' est la valeur par défaut
// si le champ n'a pas été envoyé.
// $router->post('/contact', function (App\Request $request) {
//     $name = $request->input('name', 'Anonyme');
//     return App\Response::html("<p>Merci $name, message reçu.</p>");
// });

// le formulaire pour faire fonctionner la route POST /contact
// $router->get('/contact', function (App\Request $request) {
//     return App\Response::html('
//         <form method="POST" action="/contact">
//             <input type="text" name="name" placeholder="Votre nom">
//             <button type="submit">Envoyer</button>
//         </form>
//     ');
// });


// A décommenter une fois le Router.php (avec paramètres) effectué
// $router->get('/articles/{id}', function (Request $req, array $params) {
//     $id = (int) $params['id'];
//     return Response::html("<h1>Article n°$id</h1>");
// });

// $router->get('/users/{userId}/posts/{postId}', function (Request $req, array $params) {
//     return Response::json([
//         'user'  => $params['userId'],
//         'post'  => $params['postId'],
//     ]);
// });


// A décommenter pour utiliser les controllers. Commenter les routes correspondant au dessus.
$router->get('/articles',      [ArticleController::class, 'index']);
$router->get('/articles/search', [ArticleController::class, 'search']);
$router->get('/articles/show', [ArticleController::class, 'show']);
$router->get('/articles/show/{id}', [ArticleController::class, 'show_data']);
$router->get('/articles/add',     [ArticleController::class, 'create']);
$router->post('/articles/add',     [ArticleController::class, 'add']);
$router->get('/', [Controllers\HomeController::class, 'index']);
