<?php

return [
    // Préfixe des routes générées : /api/{route_prefix}/medias
    'route_prefix' => 'admin',

    // Middleware appliqué au groupe de routes du package
    'middleware' => ['auth:sanctum'],

    // Disk Laravel utilisé pour stocker les fichiers
    'disk' => 'public',

    // Règles d'upload
    'accepted_mimes' => 'jpeg,jpg,png,gif,webp,svg',
    'max_file_size' => 2048, // Ko

    // Modèle User pour la relation uploaded_by. null = reprend
    // automatiquement auth.providers.users.model du projet hôte.
    'user_model' => null,
];