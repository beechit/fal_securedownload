<?php

declare(strict_types=1);

/*
 *  Copyright notice
 *
 *  (c) 2014 Frans Saris <frans@beech.it>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

namespace BeechIt\FalSecuredownload\Service;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\FolderInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Utility implements SingletonInterface
{
    protected static array $folderRecordCache = [];
    protected ConnectionPool $connectionPool;

    public function __construct()
    {
        $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Get folder configuration record
     *
     * @param Folder $folder
     * @return array|false
     */
    public function getFolderRecord(FolderInterface $folder)
    {
        if (!isset(self::$folderRecordCache[$folder->getCombinedIdentifier()])
            || !array_key_exists($folder->getCombinedIdentifier(), self::$folderRecordCache)
        ) {
            $queryBuilder = $this->getQueryBuilder();
            try {
                $record = $queryBuilder
                    ->select('*')
                    ->from('tx_falsecuredownload_folder')
                    ->where(
                        $queryBuilder->expr()->eq(
                            'storage',
                            $queryBuilder->createNamedParameter($folder->getStorage()->getUid(), Connection::PARAM_INT)
                        )
                    )
                    ->andWhere(
                        $queryBuilder->expr()->eq(
                            'folder_hash',
                            $queryBuilder->createNamedParameter($folder->getHashedIdentifier())
                        )
                    )
                    ->executeQuery()
                    ->fetchAssociative();
            } catch (Exception) {
                $record = false;
            }

            // cache results
            self::$folderRecordCache[$folder->getCombinedIdentifier()] = $record;
        }

        return self::$folderRecordCache[$folder->getCombinedIdentifier()];
    }

    /**
     * Update folder record after move/rename
     */
    public function updateFolderRecord(
        int $oldStorageUid,
        string $oldIdentifierHash,
        string $oldIdentifier,
        array $newRecord
    ): void {
        $allowedFields = ['storage', 'folder', 'folder_hash'];
        $record = [];

        foreach ($allowedFields as $field) {
            if (isset($newRecord[$field])) {
                $record[$field] = $newRecord[$field];
            }
        }

        if (!empty($record)) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder
                ->update('tx_falsecuredownload_folder')
                ->where(
                    $queryBuilder->expr()->eq(
                        'storage',
                        $queryBuilder->createNamedParameter($oldStorageUid, Connection::PARAM_INT)
                    )
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'folder_hash',
                        $queryBuilder->createNamedParameter($oldIdentifierHash)
                    )
                );

            foreach ($record as $field => $value) {
                $queryBuilder->set($field, $value);
            }
            $queryBuilder->executeStatement();

            // clear cache if exists
            if (isset(self::$folderRecordCache[$oldStorageUid . ':' . $oldIdentifier])) {
                unset(self::$folderRecordCache[$oldStorageUid . ':' . $oldIdentifier]);
            }
        }
    }

    /**
     * Delete folder record when folder is deleted
     */
    public function deleteFolderRecord(int $storageUid, string $folderHash, string $identifier): void
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->delete('tx_falsecuredownload_folder')
            ->where(
                $queryBuilder->expr()->eq(
                    'storage',
                    $queryBuilder->createNamedParameter($storageUid, Connection::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq('folder_hash', $queryBuilder->createNamedParameter($folderHash))
            )
            ->executeStatement();

        // clear cache if exists
        if (isset(self::$folderRecordCache[$storageUid . ':' . $identifier])) {
            unset(self::$folderRecordCache[$storageUid . ':' . $identifier]);
        }
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable('tx_falsecuredownload_folder');
    }
}
