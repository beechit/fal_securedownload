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
    'module' => '',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => true,
    'version' => '2.1.2',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0 - 8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'ke_search' => '1.8.4',
            'solrfal' => '4.1.0',
        ],
    ],
];
