<?php

namespace Controllers;

use App\Request;
use App\Response;

class ArticleController
{

    private array $contacts;

    private string $contactsFile;

    public function __construct()
    {
        $this->contactsFile = dirname(__DIR__, 2) . '/data/contacts.json';
        $this->contacts = json_decode(
            file_get_contents($this->contactsFile), true, 512, JSON_THROW_ON_ERROR
        );
    }

    public function index(Request $request): Response
    {
        $html = '<h1>Page Acceuil</h1><ul>';
        $html .= '<li><a href="/articles/show">Liste des contacts</a></li>';
        $html .= '<li><a href="/articles/search">Rechercher un contact</a></li>';
        $html .= '<li><a href="/articles/add">Ajouter un contact</a></li>';
        $html .= '</ul>';
        return Response::html($html);


    }

    public function show(Request $request, array $params): Response
    {

        $html = '<h1>Contacts</h1><ul>';
        foreach ($this->contacts as $c) {
            $c['Contact'] = htmlspecialchars($c['Contact'], ENT_QUOTES, 'UTF-8');
            $html .= "<li><a href=\"/articles/show/{$c['id']}\">{$c['Contact']}</a></li>";
        }
        $html .= '</ul>';
        return Response::html($html);

    }

    public function show_data(Request $request, array $params): Response
    {

        $id = (int) $params['id'];
        foreach ($this->contacts as $contact){
            if ($contact['id'] === $id){
                $contact['Contact'] = htmlspecialchars($contact['Contact'], ENT_QUOTES, 'UTF-8');
                $content = htmlspecialchars($contact['content'] ?? 'Aucun contenu pour ce contact', ENT_QUOTES, 'UTF-8');
                return Response::html(
                    "<h1>Contact n°$id : {$contact['Contact']}</h1>
                    <p>$content</p>"
                );
            } 
        }
        return Response::html('Contact introuvable', 404);
    }

    // Méthode pour rechercher un contact
    public function search(Request $request): Response
    {
        $html = '<form method="GET" action="/articles/search">';
        $html .= '<input type="search" name="rechercher"/>';
        $html .= '<button type="submit">Rechercher</button>';
        $html .= '</form>';

        $recherche = trim($request->query('rechercher', ''));       
        foreach ($this->contacts as $contact) {
            if ($recherche !== '' && mb_stripos($contact['Contact'], $recherche) === 0) {
                $id = $contact['id'];
                $content = htmlspecialchars($contact['content'] ?? 'Aucun contenu pour ce contact', ENT_QUOTES, 'UTF-8');
                $title = htmlspecialchars($contact['Contact'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8');
                return Response::html(
                    "<h1>Contact n°$id : $title</h1>
                    <p>$content</p>"
                );
            }
        }
        if ($recherche !== '') {
            $html .= '<p>Aucun contact trouve. Ressaisir votre recherche.</p>';
        }

        return Response::html($html);
    } 


    // Méthode pour ajouter un contact

    public function create(Request $request): Response
    {
        $html = '<h1>Ajouter un contact</h1>';
        $html .= '<form method="POST" action="/articles/add">';
        $html .= '<input type="text" name="Contact" placeholder="Nom du contact" required>';
        $html .= '<textarea name="content" placeholder="Contenu du contact" required></textarea>';
        $html .= '<button type="submit">Ajouter</button>';
        $html .= '</form>';
       return Response::html($html);
        
    }

    public function add(Request $request): Response
    {
        $contact = $request->input('Contact', '');
        $content = $request->input('content', '');
        if (!is_string($contact) || !is_string($content)
            || trim($contact) === '' || trim($content) === '') {
            return Response::html('Le nom et le contenu sont obligatoires.', 422);
        }

        // Verrouiller pendant la lecture et l'ecriture pour conserver tous les ajouts.
        $file = fopen($this->contactsFile, 'r+');
        if ($file === false) {
            return Response::html('Impossible de sauvegarder le contact.', 500);
        }
        try {
            if (!flock($file, LOCK_EX)) {
                return Response::html('Impossible de sauvegarder le contact.', 500);
            }
            $contacts = json_decode(stream_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            $contacts[] = [
                'id' => max(array_column($contacts, 'id') ?: [0]) + 1,
                'Contact' => trim($contact),
                'content' => trim($content),
            ];
            $json = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            rewind($file);
            if (fwrite($file, $json) !== strlen($json) || !ftruncate($file, strlen($json))) {
                return Response::html('Impossible de sauvegarder le contact.', 500);
            }
            fflush($file);
        } finally {
            fclose($file);
        }

        return Response::redirect('/articles/show', 303);

    }
}
