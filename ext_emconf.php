<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "fal_securedownload"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'FAL Secure Download',
    'description' => 'Secure download of assets. Makes it possible to secure FE use of assets/files by setting permissions to folders/files for fe_groups.',
    'category' => 'plugin',
    'author' => 'Frans Saris (Beech.it)',
    'author_email' => 't3ext@beech.it',
    'author_company' => 'Beech.it',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '4.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0 - 11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'ke_search' => '4.3.1',
            'solrfal' => '10.0.0',
        ],
    ],
];
