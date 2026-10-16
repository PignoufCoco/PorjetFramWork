<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Mon Framework') ?></title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 800px;
            margin: 2rem auto;
        }

        nav {
            background: #2E74B5;
            padding: 1rem;
        }

        nav a {
            color: white;
            margin-right: 1rem;
            text-decoration: none;
        }

        footer {
            margin-top: 2rem;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 1rem;
        }
    </style>
</head>

<body>
    <nav>
        <a href="/">Accueil</a>
        <a href="/articles">Articles</a>
        <a href="/about">À propos</a>
    </nav>
    <main>
        <?= $content ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> — Micro-framework pédagogique</p>
    </footer>
</body>

</html>