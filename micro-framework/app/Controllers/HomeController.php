<?php

namespace Controllers;

use App\Request;
use App\Response;
use App\View;
use App\Container;

class HomeController
{
    private View $view;

    public function __construct(Container $container)
    {
        $this->view = $container->get('view');
    }


    public function index(Request $request): Response
    {
        $html = $this->view->renderWithLayout('home/index', [
            'pageTitle' => 'Accueil',
        ]);
        return Response::html($html);
    }
}
