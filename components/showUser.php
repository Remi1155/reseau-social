<?php
function showUser($imageUrl, $prenom, $nom) {
    return '
    <div class="text-3xl flex items-center">
        <img src="' . htmlspecialchars($imageUrl) . '" alt="Image de l\'Utilisateur" class="w-16 h-16 mr-2">
        ' . htmlspecialchars($prenom . ' ' . $nom) . '
    </div>';
}