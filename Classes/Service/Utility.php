<?php
namespace BeechIt\FalSecuredownload\Service;

/***************************************************************
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
 ***************************************************************/

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Utility
 */
class Utility implements SingletonInterface
{

    static protected $folderRecordCache = [];

    /**
     * @var ConnectionPool
     */
    protected $connectionPool;

    public function __construct()
    {
        if (version_compare(TYPO3_branch, '8.7', '>=')) {
            $this->connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        }
    }

    /**
     * Get folder configuration record
     *
     * @param Folder $folder
     * @return array
     */
    public function getFolderRecord(Folder $folder)
    {

        if (!isset(self::$folderRecordCache[$folder->getCombinedIdentifier()])
            || !array_key_exists($folder->getCombinedIdentifier(), self::$folderRecordCache)
        ) {
            if (version_compare(TYPO3_branch, '8.7', '>=')) {

                $queryBuilder = $this->getQueryBuilder();
                $record = $queryBuilder
                    ->select('*')
                    ->from('tx_falsecuredownload_folder')
                    ->where($queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter((int)$folder->getStorage()->getUid(), \PDO::PARAM_INT)))
                    ->andWhere($queryBuilder->expr()->eq('folder_hash', $queryBuilder->createNamedParameter($folder->getHashedIdentifier(), \PDO::PARAM_STR)))
                    ->execute()
                    ->fetch();

            } else {

                $record = $this->getDatabase()->exec_SELECTgetSingleRow(
                    '*',
                    'tx_falsecuredownload_folder',
                    'storage = ' . (int)$folder->getStorage()->getUid() . '
                    AND folder_hash = ' . $this->getDatabase()->fullQuoteStr($folder->getHashedIdentifier(), 'tx_falsecuredownload_folder')
                );

            }
            // cache results
            self::$folderRecordCache[$folder->getCombinedIdentifier()] = $record;
        }

        return self::$folderRecordCache[$folder->getCombinedIdentifier()];
    }

    /**
     * Update folder record after move/rename
     *
     * @param int $oldStorageUid
     * @param string $oldIdentifierHash
     * @param string $oldIdentifier
     * @param array $newRecord
     */
    public function updateFolderRecord($oldStorageUid, $oldIdentifierHash, $oldIdentifier, $newRecord)
    {
        $allowedFields = ['storage', 'folder', 'folder_hash'];
        $record = [];

        foreach ($allowedFields as $field) {
            if (isset($newRecord[$field])) {
                $record[$field] = $newRecord[$field];
            }
        }

        if (!empty($record)) {
            if (version_compare(TYPO3_branch, '8.7', '>=')) {
                $queryBuilder = $this->getQueryBuilder();
                $queryBuilder
                    ->update('tx_falsecuredownload_folder')
                    ->where($queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter((int)$oldStorageUid, \PDO::PARAM_INT)))
                    ->andWhere($queryBuilder->expr()->eq('folder_hash', $queryBuilder->createNamedParameter($oldIdentifierHash, \PDO::PARAM_STR)));
                foreach ($record as $field => $value) {
                    $queryBuilder->set($field, $value);
                }
                $queryBuilder->execute();

            } else {
                $this->getDatabase()->exec_UPDATEquery(
                    'tx_falsecuredownload_folder',
                    'storage = ' . (int)$oldStorageUid . '
                     AND folder_hash = ' . $this->getDatabase()->fullQuoteStr($oldIdentifierHash, 'tx_falsecuredownload_folder'),
                    $record,
                    true
                );
            }

            // clear cache if exists
            if (isset(self::$folderRecordCache[$oldStorageUid . ':' . $oldIdentifier])) {
                unset(self::$folderRecordCache[$oldStorageUid . ':' . $oldIdentifier]);
            }
        }
    }

    /**
     * Delete folder record when folder is deleted
     *
     * @param int $storageUid
     * @param string $folderHash
     * @param string $identifier
     */
    public function deleteFolderRecord($storageUid, $folderHash, $identifier)
    {

        if (version_compare(TYPO3_branch, '8.7', '>=')) {
            $queryBuilder = $this->getQueryBuilder();
            $queryBuilder
                ->delete('tx_falsecuredownload_folder')
                ->where($queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter((int)$storageUid, \PDO::PARAM_INT)))
                ->andWhere($queryBuilder->expr()->eq('folder_hash', $queryBuilder->createNamedParameter($folderHash, \PDO::PARAM_STR)))
                ->execute();

        } else {
            $this->getDatabase()->exec_DELETEquery(
                'tx_falsecuredownload_folder',
                'storage = ' . (int)$storageUid . '
                AND folder_hash = ' . $this->getDatabase()->fullQuoteStr($folderHash, 'tx_falsecuredownload_folder')
            );
        }

        // clear cache if exists
        if (isset(self::$folderRecordCache[$storageUid . ':' . $identifier])) {
            unset(self::$folderRecordCache[$storageUid . ':' . $identifier]);
        }
    }

    /**
     * Gets the database object.
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * Gets a query build
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->connectionPool->getQueryBuilderForTable('tx_falsecuredownload_folder');
    }

}
