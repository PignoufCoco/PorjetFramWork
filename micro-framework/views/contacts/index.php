<h1>Contacts</h1>
<ul>
    <?php foreach ($contacts as $contact): ?>
        <li><a href="/articles/show/<?= e((string) $contact['id']) ?>"><?= e($contact['Contact']) ?></a></li>
    <?php endforeach; ?>
</ul>
