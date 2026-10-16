<?php

// Le conteneur d'injection de dépendances — un registre central
// qui sait comment créer chaque "service" (objet partagé) de l'application.
//
// Sans conteneur, chaque contrôleur crée ses dépendances lui-même :
//   $view = new View(__DIR__ . '/../../views');  ← chemin en dur, couplage fort
//
// Avec conteneur, les dépendances sont déclarées une seule fois
// et fournies automatiquement à ceux qui en ont besoin :
//   $view = $container->get('view');  ← le conteneur sait comment la créer

namespace App;

class Container
{
    // Les "recettes" de création: comment fabriquer chaque service.
    // Chaque recette est une fonction anonyme (callable) qui sera
    // exécutée la première fois qu'on demande le service.
    //
    // Exemple après configuration :
    // $this->factories = [
    //     'view'     => function($container) { return new View('/chemin/views'); },
    //     'router'   => function($container) { return new Router(); },
    //     'articles' => function($container) { return new ArticleRepository(); },
    // ];
    //
    // La recette n'est pas exécutée au moment du set() mais seulement
    // quand quelqu'un appelle get() pour la première fois.
    // C'est du "lazy loading" : on ne crée rien tant qu'on n'en a pas besoin.

    /** @var array<string, callable> Recettes de création */
    private array $factories = [];

    // Le cache des instances déjà créées.
    // Une fois qu'un service a été créé, on le stocke ici
    // pour ne pas le recréer à chaque appel à get().
    // C'est le pattern Singleton appliqué par service :
    // un seul objet View, un seul Router, un seul ArticleRepository
    // pour toute la durée de la requête.
    //
    // Exemple après deux appels à get('view') :
    // $this->instances = [
    //     'view' => <l'objet View créé au premier appel>,
    // ];
    // Le deuxième appel retourne le même objet, pas un nouveau.

    /** @var array<string, object> Cache des instances (singleton) */
    private array $instances = [];

    // Enregistre une recette de création pour un service.
    //
    // $name    = le nom du service ('view', 'router', 'articles'...)
    // $factory = une fonction qui crée et retourne le service.
    //            Elle reçoit le conteneur lui-même en paramètre,
    //            ce qui permet à un service de dépendre d'un autre.
    //
    // Exemple d'utilisation :
    //   $container->set('view', function($c) {
    //       return new View('/chemin/views');
    //   });
    //
    //   // Un service qui dépend d'un autre service :
    //   $container->set('articleController', function($c) {
    //       return new ArticleController($c->get('view'));
    //       //                           ^^^^^^^^^^^^^^
    //       //  Le conteneur se passe lui-même à la factory,
    //       //  qui peut demander d'autres services via get().
    //   });
    //
    // Chez Symfony, c'est l'équivalent de déclarer un service
    // dans config/services.yaml.

    public function set(string $name, callable $factory): void
    {
        $this->factories[$name] = $factory;
    }

    // Récupère un service. C'est la méthode la plus importante.
    //
    // Trois cas possibles :
    //   1. Le service a déjà été créé → retourner l'instance en cache
    //   2. Une recette existe → l'exécuter, mettre en cache, retourner
    //   3. Rien → exception
    //
    // Exemple de flux pour get('view') :
    //   Premier appel :
    //     - instances['view'] n'existe pas → on continue
    //     - factories['view'] existe → on exécute la factory
    //     - La factory retourne new View('/chemin/views')
    //     - On stocke l'objet dans instances['view']
    //     - On retourne l'objet
    //
    //   Deuxième appel :
    //     - instances['view'] existe → on retourne directement
    //     - La factory n'est pas ré-exécutée
    //     - C'est le même objet View qu'au premier appel

    public function get(string $name): object
    {
        // Cas 1 : déjà créé → retourner l'instance en cache.
        // C'est ce qui garantit le comportement singleton :
        // $container->get('view') === $container->get('view') → true
        // Même objet, même référence mémoire.
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Cas 3 : ni recette ni instance → erreur.
        // On le vérifie avant d'essayer de créer, pour avoir
        // un message d'erreur clair au lieu d'un "undefined index".
        if (!isset($this->factories[$name])) {
            throw new \RuntimeException("Service inconnu : $name");
        }

        // Cas 2 : première demande → exécuter la recette.
        //
        // ($this->factories[$name]) → récupère la closure
        // ($this)                   → la passe le conteneur en argument
        //
        // Pourquoi passer $this ? Pour que la factory puisse
        // demander d'autres services dont elle dépend :
        //   function($container) {
        //       $view = $container->get('view');  ← dépendance croisée
        //       return new ArticleController($view);
        //   }
        //
        // Le résultat est mis en cache pour les appels suivants.
        $this->instances[$name] = ($this->factories[$name])($this);
        return $this->instances[$name];
    }

    // Vérifie si un service est disponible dans le conteneur.
    // Retourne true si une recette ou une instance existe.
    //
    // Utile pour du code défensif :
    //   if ($container->has('database')) {
    //       $db = $container->get('database');
    //   }
    //
    // Chez Symfony, c'est $container->has(MonService::class).

    public function has(string $name): bool
    {
        return isset($this->factories[$name]) || isset($this->instances[$name]);
    }
}
