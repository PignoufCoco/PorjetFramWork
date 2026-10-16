<?php

namespace Controllers;

use App\Request;
use App\Response;
use App\View;
use App\Container;

class HomeController
{
    private View $view;

    // Partie à décommenter tant qu'on n'a pas de Container.php
    public function __construct()
    {
        $this->view = new View(__DIR__ . '/../../views');
    }

    // Constructeur à décommenter quand on a un Container.php.
    // Commenter le constructeur ci-dessus.
    // public function __construct(Container $container)
    // {
    //     $this->view = $container->get('view');
    // }

    public function index(Request $request): Response
    {
        $html = $this->view->renderWithLayout('home/index', [
            'pageTitle' => 'Accueil',
        ]);
        return Response::html($html);
    }
}
