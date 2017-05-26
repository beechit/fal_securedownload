<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "fal_securedownload"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'FAL Secure Download',
    'description' => 'Secure download of assets. Makes it possible to secure FE use of assets/files by setting permissions to folders/files for fe_groups.',
    'category' => 'plugin',
    'author' => 'Frans Saris (Beech.it)',
    'author_email' => 't3ext@beech.it',
    'author_company' => 'Beech.it',
    'shy' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'version' => '2.0.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '7.6.0 - 8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(
            'ke_search' => '1.8.4',
            'solrfal' => '4.1.0',
        ),
    ),
);
