<?php

/**
 * Affiche une erreur de manière sécurisée
 * Gère les erreurs sous forme de string ou de tableau
 */
function displayError($error): string
{
    if (is_array($error)) {
        return htmlspecialchars(implode(', ', $error));
    }
    return htmlspecialchars($error);
}