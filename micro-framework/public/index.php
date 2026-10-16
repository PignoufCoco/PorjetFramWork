<?php
// public/index.php
// Le Front Controller: point d'entrée unique de l'application.
// Toutes les requêtes HTTP passent par ce fichier, sans exception.
// C'est l'équivalent de public/index.php chez Symfony, mais sans
// le Runtime : on fait les étapes à la main.
//
// Pour que ça fonctionne, le serveur web doit rediriger toutes
// les URLs vers ce fichier :
//   php -S localhost:8000 -t public/    (serveur intégré PHP)
//   ou via .htaccess (Apache) / try_files (Nginx)

// ── ÉTAPE 1 : Chargement de l'autoloader ─────────────────────
// Composer a généré ce fichier avec "composer dump-autoload".
// Il permet de ne jamais écrire "require" manuellement :
// dès qu'on utilise une classe (App\Request, App\Router...),
// PHP sait dans quel fichier la trouver grâce au PSR-4
// déclaré dans composer.json.
require_once __DIR__ . '/../vendor/autoload.php';

// "use" crée des raccourcis de noms de classes.
// Sans ça, il faudrait écrire App\Request partout.
// Ce n'est pas un import => la classe n'est pas chargée ici.
// Elle sera chargée automatiquement par l'autoloader
// au moment où on l'utilise pour la première fois.
use App\Request;

// A décommenter pour le Router sans paramètres (et commenter Router)
//use App\RouterSimple;

// A décommenter pour le Router avec paramètres (et commenter RouterSimple)
use App\Router;


// ── ÉTAPE 2 : Bootstrap ──────────────────────────────────────
// Créer le routeur (vide pour l'instant, aucune route déclarée).
// Chez Symfony, cette étape correspond au boot() du Kernel :
// création du conteneur de services, chargement de la config, etc.
// Ici c'est beaucoup plus simple : juste un new.

// A décommenter pour le Router sans paramètres (et commenter Router)
//$router = new RouterSimple();

// A décommenter pour le Router avec paramètres (et commenter RouterSimple)
$router = new router();

// Charger le fichier de routes.
// Ce require exécute config/routes.php, qui appelle
// $router->get(...) et $router->post(...) pour enregistrer
// les routes dans le routeur qu'on vient de créer.
// La variable $router est accessible dans routes.php
// parce que require partage le scope du fichier appelant.
// Chez Symfony, c'est config/routes.yaml + les attributs #[Route].
require_once __DIR__ . '/../config/routes.php';

// ── ÉTAPE 3 : Réception de la requête ────────────────────────
// Lire les superglobales PHP ($_SERVER, $_GET, $_POST)
// et les encapsuler dans un objet Request.
// Après cette ligne, plus personne ne touche aux superglobales.
// Chez Symfony, c'est Request::createFromGlobals() dans le Runtime.
$request = Request::fromGlobals();

// ── ÉTAPES 4-5 : Routage + Exécution du contrôleur ──────────
// Le routeur reçoit la Request, cherche la route correspondante,
// exécute le handler, et retourne un objet Response.
// Une seule ligne qui fait tout ce que Symfony répartit entre
// le RouterListener, le ControllerResolver, l'ArgumentResolver
// et l'appel du contrôleur.
// Si aucune route ne correspond → Response 404 automatique.
$response = $router->resolve($request);

// ── ÉTAPE 6 : Envoi de la réponse ────────────────────────────
// Envoie le code HTTP, les headers et le body au navigateur.
// Après cette ligne, le navigateur reçoit la page.
// Chez Symfony, c'est $response->send() dans le HttpKernelRunner,
// suivi de terminate() pour le nettoyage (qu'on n'a pas ici).
$response->send();




// Version avec Application.php fonctionnel. Commenter tout ce qu'il y a au dessus de cette ligne
// require_once __DIR__ . '/../vendor/autoload.php';
// $app = new App\Application(dirname(__DIR__));
// $router = $app->router();
// require __DIR__ . '/../config/routes.php';
// $app->run();

// Version finale avec Application.php fonctionnel et Container.php. Commenter tout ce qu'il y a au dessus de cette ligne et décommenter tout ce qui est en dessous
// require_once __DIR__ . '/../vendor/autoload.php';
// $app = new App\Application(dirname(__DIR__));
// $router = $app->router();
// require __DIR__ . '/../config/routes.php';
// $app->run();
