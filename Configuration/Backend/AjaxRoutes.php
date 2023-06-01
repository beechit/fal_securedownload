<?php

use BeechIt\FalSecuredownload\Controller\BePublicUrlController;

return [
    'dump_file' => [
        'path' => '/fal_securedownloads/dump_file',
        'target' => BePublicUrlController::class . '::dumpFile'
    ]
];
