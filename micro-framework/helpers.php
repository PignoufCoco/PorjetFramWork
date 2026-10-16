<?php

/**
 * Échappe une chaîne pour un affichage sûr en HTML.
 * Équivalent du filtre |escape de Twig.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
