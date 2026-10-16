<?php

namespace App;

use Controllers\ArticleController;
use Controllers\HomeController;

class Application
{
    private Container $container;
    private Router $router;

    public function __construct(string $basePath)
    {
        $this->container = new Container();
        $this->container->set('view', function () use ($basePath) {
            return new View($basePath . '/views');
        });
        $this->container->set('router', function () {
            return new Router();
        });

        // Le conteneur fournit les dependances de chaque controleur.
        $this->container->set(HomeController::class, function (Container $container) {
            return new HomeController($container);
        });
        $this->container->set(ArticleController::class, function (Container $container) {
            return new ArticleController($container);
        });

        $this->router = $this->container->get('router');
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request = Request::fromGlobals();
        $response = $this->router->resolve($request, $this->container);
        $response->send();
    }
}
