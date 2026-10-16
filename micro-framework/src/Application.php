<?php

namespace App;

// Partie à décommenter tant qu'on n'a pas de Container.php
class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function run(): void
    {
        $request  = Request::fromGlobals();
        $response = $this->router->resolve($request);
        $response->send();
    }
}

// Partie à décommenter quand on a un Container.php.
// Commenter la classe ci-dessus.
// class Application
// {
//     private Container $container;
//     private Router $router;

//     public function __construct(string $basePath)
//     {
//         $this->container = new Container();
//         $this->router = new Router();

//         $this->container->set('view', function () use ($basePath) {
//             return new View($basePath . '/views');
//         });

//         $this->container->set('router', function () {
//             return $this->router;
//         });
//     }

//     public function container(): Container
//     {
//         return $this->container;
//     }
//     public function router(): Router
//     {
//         return $this->router;
//     }

//     public function run(): void
//     {
//         $request  = Request::fromGlobals();
//         $response = $this->router->resolve($request, $this->container);
//         $response->send();
//     }
// }
