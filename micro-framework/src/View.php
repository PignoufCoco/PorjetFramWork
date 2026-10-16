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

    // Rend un template et retourne le HTML sous forme de chaîne.
    //
    // $view = le chemin relatif de la vue, sans le .php
    //   'home/index'      → views/home/index.php
    //   'articles/show'   → views/articles/show.php
    //   'layouts/main'    → views/layouts/main.php
    //
    // $data = les variables à injecter dans le template
    //   ['title' => 'Accueil', 'articles' => [...]]
    //
    // Retourne une string (pas une Response). C'est le contrôleur
    // qui décidera quoi en faire (Response::html(), etc.).
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

        // ── Injection des données dans le template ───────────────
        //
        // extract() prend un tableau associatif et crée une variable
        // locale pour chaque clé :
        //
        //   $data = ['title' => 'Accueil', 'name' => 'Alice']
        //
        //   Après extract($data) :
        //     $title = 'Accueil'    ← accessible dans le template
        //     $name  = 'Alice'      ← accessible dans le template
        //
        // C'est comme si on avait écrit manuellement :
        //   $title = $data['title'];
        //   $name  = $data['name'];
        //
        // Mais extract() le fait automatiquement pour toutes les clés.
        // Chez Symfony/Twig, c'est le compilateur Twig qui fait
        // la même chose en transformant {{ title }} en echo $title.
        extract($data);

        // ── Capture du HTML généré ───────────────────────────────
        //
        // Le problème : require exécute le fichier PHP et affiche
        // directement le résultat (echo vers le navigateur).
        // Mais on ne veut pas afficher tout de suite, on veut
        // récupérer le HTML comme une chaîne pour pouvoir
        // l'insérer dans un layout ou le passer à Response::html().
        //
        // La solution : le tampon de sortie (output buffer).
        //
        // ob_start() ouvre un tampon : à partir de maintenant,
        // tout ce que PHP afficherait (echo, HTML hors balises PHP)
        // est capturé en mémoire au lieu d'être envoyé au navigateur.
        ob_start();

        // require exécute le template PHP.
        // Le template peut utiliser les variables créées par extract() :
        //   <h1><?= e($title) ? ></h1>
        //   <p>Par <?= e($name) ? ></p>
        //
        // Tout le HTML généré est capturé dans le tampon,
        // et non envoyé au navigateur.
        require $filePath;

        // ob_get_clean() fait deux choses en une :
        //   1. Récupère tout le contenu du tampon (le HTML généré)
        //   2. Ferme et vide le tampon
        //
        // On retourne le HTML comme une simple chaîne de caractères.
        // Le contrôleur pourra ensuite faire :
        //   return Response::html($html);
        return ob_get_clean();
    }


    // Rend une vue dans un layout (gabarit commun).
    //
    // Le problème : chaque page du site partage la même structure HTML
    // (DOCTYPE, head, nav, footer). Sans layout, on copierait ce HTML
    // dans chaque vue — violation du principe DRY.
    //
    // La solution : deux fichiers séparés.
    //   1. La vue enfant (ex: 'quotes/index') → juste le contenu spécifique
    //   2. Le layout (ex: 'layouts/main') → la structure commune avec un
    //      trou au milieu (<?= $content ? >) où le contenu sera injecté.
    //
    // Paramètres :
    //   $view   = la vue enfant ('quotes/index', 'home/about'...)
    //   $data   = les variables pour la vue (['pageTitle' => '...', 'quotes' => [...]])
    //   $layout = le layout à utiliser (par défaut 'layouts/main')
    //
    // Exemple d'appel dans un contrôleur :
    //   $html = $this->view->renderWithLayout('quotes/index', [
    //       'pageTitle' => 'Citations',
    //       'quotes'    => $quotes,
    //   ]);

    public function renderWithLayout(
        string $view,
        array $data = [],
        string $layout = 'layouts/main'
    ): string {

        // Étape 1 : rendre la vue enfant.
        // On obtient le HTML du contenu spécifique de la page.
        // Ex : '<h1>Citations</h1><ul><li>...</li></ul>'
        // Les variables de $data sont injectées dans la vue enfant
        // ($pageTitle, $quotes, etc.)
        $content = $this->render($view, $data);

        // Étape 2 : rendre le layout en lui passant le contenu.
        // array_merge combine les données originales ($data) avec
        // la nouvelle variable 'content' qui contient le HTML de l'étape 1.
        //
        // Résultat de array_merge :
        //   ['pageTitle' => 'Citations', 'quotes' => [...], 'content' => '<h1>Citations</h1>...']
        //
        // Dans le layout, on a accès à :
        //   $pageTitle → pour le <title> dans le <head>
        //   $content   → le HTML de la vue enfant, injecté dans <main><?= $content ? ></main>
        //
        // On passe aussi les autres variables ($data) au layout
        // au cas où il en aurait besoin (le titre de la page par exemple).
        //
        // IMPORTANT : $content n'est pas échappé avec e() dans le layout
        // parce que c'est du HTML de confiance, produit par notre propre
        // moteur de vues. L'échappement se fait dans la vue enfant,
        // sur les données utilisateur.
        return $this->render($layout, array_merge($data, ['content' => $content]));
    }
}
