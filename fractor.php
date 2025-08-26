<?php

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/Resources',
    ])
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);
