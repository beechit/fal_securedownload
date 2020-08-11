<?php
return [
    'frontend' => [
        'fal-secure-download/secure-download-middleware' => [
            'target' => \BeechIt\FalSecuredownload\Middleware\SecureDownloadMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
        'fal-secure-download/file-tree-state-middleware' => [
            'target' => \BeechIt\FalSecuredownload\Middleware\FileTreeStateMiddleware::class,
            'after' => [
                'typo3/cms-frontend/authentication',
            ],
        ],
    ],
];