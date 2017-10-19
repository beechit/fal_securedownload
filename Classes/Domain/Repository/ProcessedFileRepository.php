<?php
namespace BeechIt\FalSecuredownload\Domain\Repository;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * ProcessedFileRepository
 *
 * @package BeechIt\FalSecuredownload\Domain\Repository
 */
class ProcessedFileRepository extends \TYPO3\CMS\Core\Resource\ProcessedFileRepository
{

    /**
     * Find ProcessedFile by Uid
     *
     * @param int $uid
     * @return object|\TYPO3\CMS\Core\Resource\ProcessedFile
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function findByUid($uid)
    {
        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            throw new \InvalidArgumentException('uid has to be integer.', 1316779798);
        }

        if (version_compare(TYPO3_branch, '8.7', '>=')) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
            $row = $queryBuilder
                ->select('*')
                ->from($this->table)
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter((int)$uid, \PDO::PARAM_INT)))
                ->execute()
                ->fetch();
        } else {
            $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . (int)$uid);
        }

        if (empty($row) || !is_array($row)) {
            throw new \RuntimeException(
                'Could not find row with uid "' . $uid . '" in table ' . $this->table,
                1314354065
            );
        }
        return $this->createDomainObject($row);
    }
}
