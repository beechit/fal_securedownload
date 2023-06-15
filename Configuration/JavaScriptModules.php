<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'tags' => [
        'backend.contextmenu',
    ],
    'imports' => [
        '@beechit/fal-securedownload/' => 'EXT:fal_securedownload/Resources/Public/JavaScript/',
    ],
];
