<?php

return [
    'frontend' => [
        'beechit/eid-frontend/authentication' => [
            'target' => \BeechIt\FalSecuredownload\Middleware\EidFrontendAuthentication::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/eid'
            ],
        ],
    ]
];
