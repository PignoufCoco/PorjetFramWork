<?php

namespace Controllers;

use App\Container;
use App\Request;
use App\Response;
use App\View;

class ArticleController
{
    private View $view;
    private array $contacts;
    private string $contactsFile;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
        $this->contactsFile = dirname(__DIR__, 2) . '/data/contacts.json';
        $this->contacts = json_decode(
            file_get_contents($this->contactsFile), true, 512, JSON_THROW_ON_ERROR
        );
    }

    public function index(Request $request): Response
    {
        $html = '<h1>Page d\'accueil Contact</h1><ul>';
        $html .= '<li><a href="/articles/show">Liste des contacts</a></li>';
        $html .= '<li><a href="/articles/search">Rechercher un contact</a></li>';
        $html .= '<li><a href="/articles/add">Ajouter un contact</a></li>';
        $html .= '</ul>';
        return Response::html($html);
    }

    public function show(Request $request, array $params): Response
    {
        return Response::html($this->view->renderWithLayout('contacts/index', [
            'pageTitle' => 'Contacts',
            'contacts' => $this->contacts,
        ]));
    }

    public function show_data(Request $request, array $params): Response
    {
        $id = (int) $params['id'];
        foreach ($this->contacts as $contact) {
            if ($contact['id'] === $id) {
                return Response::html($this->view->renderWithLayout('contacts/show', [
                    'pageTitle' => $contact['Contact'],
                    'contact' => $contact,
                ]));
            }
        }
        return Response::html('Contact introuvable', 404);
    }

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
                $content = htmlspecialchars($contact['content'] ?? 'Aucun contenu pour ce contact');
                $title = htmlspecialchars($contact['Contact'] ?? 'Sans titre');
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

    public function create(Request $request): Response
    {
        return Response::html($this->view->renderWithLayout('contacts/create', [
            'pageTitle' => 'Ajouter un contact',
        ]));
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
        $contacts = json_decode(stream_get_contents($file));
        $contacts[] = [
            'id' => max(array_column($contacts, 'id') ?: [0]) + 1,
            'Contact' => trim($contact),
            'content' => trim($content),
        ];
        $json = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        rewind($file);
        fwrite($file, $json);
        fclose($file);
        
        
        return Response::redirect('/articles/show', 303);

    }
}
