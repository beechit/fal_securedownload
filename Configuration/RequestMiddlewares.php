<?php

use BeechIt\FalSecuredownload\Middleware\EidFrontendAuthentication;

return [
    'frontend' => [
        'beechit/eid-frontend/authentication' => [
            'target' => EidFrontendAuthentication::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/eid'
            ],
        ],
    ]
];
