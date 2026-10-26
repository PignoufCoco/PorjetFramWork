# Architecture du micro-framework

Ce projet est un petit framework PHP utilisé pour créer un carnet de contacts.
L'application permet de consulter les contacts, de rechercher un contact et d'en
ajouter un. Les données sont enregistrées dans `micro-framework/data/contacts.json`.

Le projet suit une organisation MVC : les données représentent le modèle, les
vues affichent les pages et les contrôleurs traitent les demandes. Ici, la gestion
des données reste directement dans `ArticleController`, sans classe modèle séparée.

## 1. Le parcours d'une requête

Prenons l'exemple d'un utilisateur qui ouvre `/articles/show/1` pour consulter
un contact.

1. La requête arrive dans `public/index.php`, le point d'entrée de l'application.
   Ce fichier charge l'autoload de Composer, qui permet de charger les classes PHP.
2. Il crée un objet `Application`. Celui-ci prépare le conteneur de services et
   récupère le routeur. Le fichier `config/routes.php` enregistre ensuite les routes.
3. La méthode `Application::run()` crée un objet `Request` à partir des informations
   envoyées par le navigateur : méthode HTTP, adresse et données du formulaire.
4. Le routeur cherche la route qui correspond à la méthode et à l'adresse.
   Ici, il reconnaît `/articles/show/{id}` et récupère la valeur `1`.
5. Le routeur récupère `ArticleController` dans le conteneur et appelle sa méthode
   `show_data()` avec la requête et le paramètre `id`.
6. Le contrôleur cherche le contact dans les données chargées depuis le fichier JSON.
   Il transmet ce contact à la vue `contacts/show.php`.
7. La classe `View` produit le HTML de la vue, puis l'insère dans le layout commun
   `layouts/main.php`.
8. Le contrôleur retourne un objet `Response`. L'application appelle `send()` pour
   envoyer le code HTTP, les en-têtes et le contenu au navigateur.

Si aucune route ne correspond, le routeur renvoie une réponse 404. Si le contact
est introuvable, le contrôleur renvoie également une réponse 404.

Pour ajouter un contact, le formulaire envoie une requête POST à `/articles/add`.
Le contrôleur vérifie que le nom et le contenu sont renseignés, enregistre le
contact dans le fichier JSON, puis redirige vers la liste.

## 2. Le rôle des classes

| Classe | Rôle |
| --- | --- |
| `Application` | Prépare les services et lance le traitement de la requête jusqu'à l'envoi de la réponse. |
| `Router` | Enregistre les routes, reconnaît l'adresse demandée et appelle la bonne méthode du contrôleur. |
| `Request` | Regroupe les informations reçues : méthode HTTP, adresse, paramètres GET et données POST. |
| `Response` | Prépare et envoie une réponse HTML, JSON ou une redirection, avec son code HTTP. |
| `View` | Charge une vue PHP avec ses données et peut l'intégrer dans un layout commun. |
| `Container` | Conserve les fonctions de création des services et réutilise les objets déjà créés pendant la requête. |
| `HomeController` | Affiche la page d'accueil. |
| `ArticleController` | Gère les contacts : liste, détail, recherche et ajout dans le fichier JSON. |

Malgré son nom, `ArticleController` gère bien les contacts dans cette application.
`RouterSimple` est une autre version du routeur présente dans le projet ; le
point d'entrée actuel utilise `Router`.

Les fichiers du dossier `views` contiennent le HTML des pages. Le layout commun
contient la navigation et le pied de page. La fonction `e()`, dans `helpers.php`,
sert à afficher les données comme du texte, pour éviter qu'elles soient
interprétées comme du code HTML.

## 3. L'injection de dépendances

Une dépendance est un objet dont une classe a besoin pour fonctionner.
Par exemple, les contrôleurs utilisent un objet `View` pour afficher les pages.

Au lieu de créer eux-mêmes cet objet avec `new View(...)`, les contrôleurs
reçoivent le conteneur dans leur constructeur et récupèrent le service `view`.
La création de ce service et le chemin des vues sont définis dans `Application`.
Le routeur utilise aussi le conteneur pour obtenir les contrôleurs.

Cela réduit le couplage : les contrôleurs n'ont pas besoin de connaître le chemin
du dossier des vues ni la façon de créer l'objet `View`. Si ce chemin change,
il suffit de modifier sa configuration dans `Application`.

Cette solution reste simple : les contrôleurs dépendent encore du conteneur et
de la classe `View`. Une amélioration serait de leur fournir directement les
services nécessaires dans leur constructeur.

## 4. Les limites et les améliorations possibles

- **Stockage des contacts :** le fichier JSON convient à une petite application.
  L'écriture actuelle n'est pas verrouillée : deux ajouts simultanés peuvent se
  gêner. On pourrait sécuriser l'écriture ou utiliser une base de données.
- **Gestion des données :** la lecture et l'écriture sont dans le contrôleur.
  Une classe dédiée permettrait de séparer le stockage du traitement des pages.
- **Validation et sécurité :** l'ajout vérifie seulement que les champs sont
  renseignés. On pourrait limiter leur longueur et ajouter une protection CSRF
  pour vérifier qu'une soumission vient bien du formulaire de l'application.
- **Affichage :** la page de recherche et le menu des contacts produisent encore
  leur HTML dans le contrôleur. On pourrait les déplacer dans des vues.
- **Gestion des erreurs :** certaines erreurs, comme un fichier JSON invalide,
  ne sont pas traitées par une page d'erreur commune. Le traitement du code 405
  (méthode HTTP non autorisée) reste aussi à compléter dans le routeur.
- **Vérification du projet :** des tests automatisés permettraient de vérifier
  les routes, les erreurs et l'ajout de contacts après chaque modification.
