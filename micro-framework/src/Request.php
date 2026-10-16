<?php
// src/Request.php
// Cette classe encapsule la requête HTTP dans un objet.
// Au lieu de manipuler directement $_GET, $_POST, $_SERVER
// (les superglobales PHP), on les range ici une seule fois.
// Équivalent simplifié de Symfony\Component\HttpFoundation\Request.

namespace App;

class Request
{
    // La méthode HTTP : GET, POST, PUT, DELETE...
    private string $method;

    // L'URI demandée : /articles/42 (sans la query string ?page=2)
    private string $uri;

    // Les paramètres d'URL (query string)
    // Ex : /search?q=symfony&page=2 → ['q' => 'symfony', 'page' => '2']
    private array $queryParams;

    // Les données du corps de la requête
    // Ex : formulaire avec <input name="email"> → ['email' => 'alice@mail.com']
    private array $bodyParams;

    // Constructeur manuel — utile pour les tests unitaires.
    // On peut créer une "fausse" requête sans serveur web :
    // new Request('GET', '/articles/42') simule un GET sur /articles/42
    public function __construct(string $method, string $uri, array $query = [], array $body = [])
    {
        // Normaliser la méthode en majuscules (get → GET)
        $this->method = strtoupper($method);

        // parse_url() extrait uniquement le chemin de l'URI.
        // '/articles/42?page=2' → '/articles/42'
        // On retire la query string car elle est déjà dans $query.
        // Si parse_url échoue (URI invalide), on retourne '/' par défaut.
        $this->uri = parse_url($uri, PHP_URL_PATH) ?: '/';

        $this->queryParams = $query;
        $this->bodyParams = $body;
    }

    // Factory method : crée une Request à partir de la vraie requête HTTP.
    // C'est cette méthode qui lit les superglobales une seule et unique fois.
    // Après cet appel, plus personne ne touche à $_GET, $_POST, $_SERVER.
    // Équivalent de Request::createFromGlobals() chez Symfony.
    public static function fromGlobals(): self
    {
        return new self(
            $_SERVER['REQUEST_METHOD'],  // GET, POST, PUT, DELETE...
            $_SERVER['REQUEST_URI'],     // /articles/42?page=2
            $_GET,                       // ['page' => '2']
            $_POST                       // ['email' => '...'] si formulaire
        );
    }

    // Accesseurs : chaque donnée a sa méthode dédiée.
    // On ne donne pas accès aux tableaux bruts : c'est de l'encapsulation.

    // Retourne la méthode HTTP (GET, POST...)
    public function method(): string
    {
        return $this->method;
    }

    // Retourne l'URI nettoyée (/articles/42, sans query string)
    public function uri(): string
    {
        return $this->uri;
    }

    // Récupère un paramètre de la query string ($_GET).
    // $request->query('page', 1)  retourne la valeur de ?page=,
    // ou 1 si le paramètre n'existe pas.
    public function query(string $key, $default = null)
    {
        return $this->queryParams[$key] ?? $default;
    }

    // Récupère un paramètre du corps de la requête ($_POST).
    // $request->input('email', 'inconnu') retourne la valeur du champ,
    // ou 'inconnu' si le champ n'a pas été envoyé.
    // L'opérateur ?? (null coalescing) retourne $default si la clé
    // n'existe pas ou si sa valeur est null.
    public function input(string $key, $default = null)
    {
        return $this->bodyParams[$key] ?? $default;
    }
}
