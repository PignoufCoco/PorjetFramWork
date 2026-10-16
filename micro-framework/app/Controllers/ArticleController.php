<?php

namespace Controllers;

use App\Request;
use App\Response;

class ArticleController
{

    private array $articles = [
        ['id' => 1, 'Contact' => 'Poupée'],
        ['id' => 2, 'Contact' => 'Robot télécommandé'],
        ['id' => 3, 'Contact' => 'ballon de foot'],
        ['id' => 4, 'Contact' => 'Marteau','content' => 'Contenu de l\'article pour le marteau.'],
    ];

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
        foreach ($this->articles as $a) {
            $html .= "<li><a href=\"/articles/show/{$a['id']}\">{$a['Contact']}</a></li>";
        }
        $html .= '</ul>';
        return Response::html($html);

    }

    public function show_data(Request $request, array $params): Response
    {

        $id = (int) $params['id'];
        foreach ($this->articles as $article){
            if ($article['id'] === $id){
                $content = $article['content'] ?? 'Aucun contenu pour ce contact';
                return Response::html(
                    "<h1>Contact n°$id : {$article['Contact']}</h1>
                    <p>$content</p>"
                );
            } 
        }
        return Response::html('Contact introuvable', 404);

    }

    

    // Méthode pour rechercher un nouvel article
    public function search(Request $request): Response
    {
        $html = '<form method="GET" action="/articles/search">';
        $html .= '<input type="search" name="rechercher"/>';
        $html .= '<button type="submit">Rechercher</button>';
        $html .= '</form>';

        $recherche = $request->query('rechercher', '');        
        foreach ($this->articles as $article){
            if ($article['Contact'] === $recherche){
                $id = $article['id'];
                $content = $article['content'] ?? 'Aucun contenu pour ce contact';
                $title = $article['Contact'] ?? 'Sans titre';
                return Response::html(
                    "<h1>Contact n°$id : $title</h1>
                    <p>$content</p>"
                );
            } 
        }
        return Response::html($html);


    }

    public function add(Request $request): Response
    {
        $contact = $request->input('Contact', 'Sans contact');
        return Response::json(['created' => true, 'Contact' => $contact], 201);
    }
}
