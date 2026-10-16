<?php
// src/Response.php
// Cette classe encapsule la réponse HTTP dans un objet.
// Au lieu d'appeler directement echo, header() et http_response_code()
// (les fonctions PHP natives), on prépare tout dans un objet
// et on envoie d'un coup à la fin.
// Équivalent simplifié de Symfony\Component\HttpFoundation\Response.

namespace App;

class Response
{
    // Le contenu de la réponse: le HTML, le JSON ou rien (redirection).
    // C'est ce que le navigateur affichera.
    private string $body;

    // Le code HTTP: indique au navigateur si tout va bien (200),
    // si la page n'existe pas (404), si on redirige (302), etc.
    // Par défaut : 200 (OK).
    private int $statusCode;

    // Les en-têtes HTTP: des métadonnées envoyées avant le contenu.
    // Ils disent au navigateur : quel type de contenu (HTML, JSON),
    // quel encodage (UTF-8), où rediriger (Location), etc.
    // Le navigateur ne les affiche pas, il les utilise en interne.
    private array $headers;

    // Constructeur avec des valeurs par défaut raisonnables :
    // body vide, code 200, aucun header.
    // Permet de créer une réponse à la carte :
    // new Response('<h1>OK</h1>', 200, ['Content-Type' => 'text/html'])
    public function __construct(string $body = '', int $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    // Envoie effectivement la réponse au navigateur.
    // C'est le seul endroit du framework qui utilise les fonctions
    // PHP natives d'envoi. Tout le reste manipule l'objet Response.
    // Équivalent de $response->send() chez Symfony.
    public function send(): void
    {
        // 1. Envoyer le code HTTP (200, 404, 302...)
        // Le navigateur reçoit "HTTP/1.1 200 OK" ou "HTTP/1.1 404 Not Found"
        http_response_code($this->statusCode);

        // 2. Envoyer chaque en-tête HTTP
        // header() est une fonction PHP native qui ajoute une ligne
        // dans les en-têtes de la réponse.
        // Ex : header("Content-Type: text/html; charset=UTF-8")
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }

        // 3. Envoyer le contenu (HTML, JSON, texte...)
        // C'est ce qui apparaît dans le navigateur.
        echo $this->body;
    }

    // ── Factory methods ─────────────────────────────────────────
    // Raccourcis pour créer les types de réponses les plus courants.
    // Au lieu d'écrire new Response('<h1>...</h1>', 200, ['Content-Type' => ...])
    // à chaque fois, on écrit Response::html('<h1>...</h1>').
    // Le "self" retourné signifie "une instance de Response",
    // ça permet le chaînage et l'uniformité.

    // Réponse HTML
    // Le header Content-Type dit au navigateur : "interprète ça comme du HTML".
    // charset=UTF-8 garantit l'affichage correct des accents (é, è, ê...).
    public static function html(string $html, int $code = 200): self
    {
        return new self($html, $code, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // Réponse JSON (pour les APIs ou les requêtes AJAX)
    // json_encode() transforme un tableau PHP en chaîne JSON :
    // ['name' => 'Alice', 'age' => 25] → '{"name":"Alice","age":25}'
    // Le header Content-Type dit au navigateur : "c'est du JSON, pas du HTML".
    public static function json(array $data, int $code = 200): self
    {
        return new self(json_encode($data), $code, ['Content-Type' => 'application/json']);
    }

    // Redirection
    // Le body est vide (rien à afficher), le code 302 signifie
    // "redirection temporaire", et le header Location indique l'URL cible.
    // Quand le navigateur reçoit un 302 avec Location: /articles,
    // il fait automatiquement une nouvelle requête GET vers /articles.
    // Code 301 = redirection permanente (le navigateur met en cache).
    // Code 302 = redirection temporaire (le navigateur re-demande à chaque fois).
    public static function redirect(string $url, int $code = 302): self
    {
        return new self('', $code, ['Location' => $url]);
    }
}
