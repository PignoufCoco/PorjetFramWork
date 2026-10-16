<?php
// src/View.php
// Le moteur de vues qui sert à séparer le HTML du code PHP.
// Au lieu que les contrôleurs construisent le HTML dans des chaînes,
// ils préparent les données et les passent à un fichier template.
// C'est notre version simplifiée de Twig chez Symfony.
//
// Twig :     {{ title }}         → échappement automatique
// Notre vue : <?= e($title) ? >  → échappement manuel avec e()
//
// Le principe est le même : le contrôleur donne les données,
// le template les affiche sans contenir de logique métier.

namespace App;

class View
{
    // Le chemin absolu vers le dossier qui contient les templates.
    // Ex : '/home/antoine/micro-framework/views'
    // Tous les chemins de vues seront relatifs à ce dossier.
    private string $viewsPath;

    // Le constructeur reçoit le chemin du dossier des vues.
    // rtrim($viewsPath, '/') retire le / final s'il y en a un,
    // pour éviter un double slash quand on construit le chemin :
    //   '/views/' + '/' + 'home/index.php' → '/views//home/index.php' => pas bon, double /
    //   '/views'  + '/' + 'home/index.php' → '/views/home/index.php'  => là c'est OK
    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/');
    }

    public function render(string $view, array $data = []): string
    {
        // Construire le chemin complet du fichier template.
        // 'articles/show' → '/chemin/views/articles/show.php'
        $filePath = $this->viewsPath . '/' . $view . '.php';

        // Vérifier que le fichier existe avant d'essayer de l'inclure.
        // Sans cette vérification, PHP afficherait un warning nébuleux
        // "failed to open stream". Avec, on a un message d'erreur clair.
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Vue introuvable : $view ($filePath)");
        }

        extract($data);
        ob_start();

    
        require $filePath;

       
        return ob_get_clean();
    }

    public function renderWithLayout(
        string $view,
        array $data = [],
        string $layout = 'layouts/main'
    ): string {
        $content = $this->render($view, $data);
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }
}
