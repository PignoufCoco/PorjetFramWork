<?php
// src/Router.php
// Le routeur est le cœur du framework, c'est lui qui fait le lien
// entre une URL et le code qui doit s'exécuter.
// Il fait en une seule classe ce que Symfony répartit entre
// le Router, le RouterListener, le ControllerResolver et l'ArgumentResolver.
// Deux responsabilités : enregistrer des routes, puis résoudre une requête.

namespace App;

class RouterSimple
{
    // La table de routage: un tableau à deux niveaux :
    // Premier niveau : la méthode HTTP (GET, POST, PUT, DELETE)
    // Deuxième niveau : le pattern d'URL → le handler (code à exécuter)
    //
    // Exemple après enregistrement de 3 routes :
    // $this->routes = [
    //     'GET'  => [
    //         '/'        => function($request) { ... },
    //         '/about'   => function($request) { ... },
    //     ],
    //     'POST' => [
    //         '/contact' => function($request) { ... },
    //     ],
    // ];
    //
    // sans ce double niveau, si on a deux routes GET /articles
    // et POST /articles elles s'écraseraient et on aurait qu'une route au lieu
    // de 2 différentes 

    /** @var array<string, array<string, callable>> */
    private array $routes = [];

    // ── Enregistrement des routes ────────────────────────────────

    // Méthode générique: enregistre une route pour n'importe quelle
    // méthode HTTP. On normalise en majuscules pour éviter que
    // 'get', 'Get' et 'GET' créent trois entrées différentes.
    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }

    // Raccourci pour GET
    // Au lieu d'écrire $router->add('GET', '/about', ...) à chaque fois,
    // on écrit $router->get('/about', ...).
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    // Raccourci pour POST
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    // ── Résolution d'une requête ─────────────────────────────────
    // C'est ici que tout se passe. Cette méthode reçoit une Request
    // et retourne toujours une Response même en cas d'erreur.
    // C'est l'équivalent simplifié de tout le cycle Symfony :
    // RouterListener::onKernelRequest() + ControllerResolver + appel du contrôleur.

    public function resolve(Request $request): Response
    {
        // Extraire la méthode et l'URI depuis l'objet Request.
        // On ne touche jamais aux superglobales ici, tout vient de Request.
        $method = $request->method();
        $uri    = $request->uri();

        // Chercher une correspondance exacte dans la table de routage.
        // isset() vérifie que :
        //   1. Il existe des routes pour cette méthode HTTP (ex: GET)
        //   2. Parmi ces routes, il en existe une pour cette URI exacte
        // C'est une recherche en O(1) (accès direct par clé de tableau).
        if (isset($this->routes[$method][$uri])) {

            // On a trouvé le handler correspondant => on l'exécute.
            // $handler est un callable : closure, fonction, ou [Classe, méthode].
            // On lui passe la Request pour qu'il puisse lire les données.
            $handler = $this->routes[$method][$uri];
            $result  = $handler($request);

            // Le handler peut retourner soit un objet Response (recommandé),
            // soit une simple chaîne de caractères (raccourci pratique).
            // Si c'est déjà une Response, on la retourne telle quelle.
            if ($result instanceof Response) {
                return $result;
            }

            // Si c'est une chaîne, on l'enveloppe dans une Response HTML.
            // (string) force la conversion au cas où le handler
            // retournerait un int ou un autre type.
            return Response::html((string) $result);
        }

        // Aucune route trouvée → erreur 404.
        // On retourne une Response valide (pas une exception).
        // Le framework ne plante jamais, il retourne toujours une réponse.
        // Limitation : cette version ne distingue pas le 404 (URL inconnue)
        // du 405 (URL connue mais mauvaise méthode HTTP).
        return Response::html('<h1>404 — Page non trouvée</h1>', 404);
    }
}
