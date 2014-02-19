<?php
namespace BeechIt\FalSecuredownload\Domain\Repository;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 18-02-2014 14:25
 * All code (c) Beech Applications B.V. all rights reserved
 */
class ProcessedFileRepository extends \TYPO3\CMS\Core\Resource\ProcessedFileRepository {

	/**
	 * Find ProcessedFile by Uid
	 * @param int $uid
	 * @return object|\TYPO3\CMS\Core\Resource\ProcessedFile
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function findByUid($uid) {
		if (!\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($uid)) {
			throw new \InvalidArgumentException('uid has to be integer.', 1316779798);
		}
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . (int)$uid);
		if (empty($row) || !is_array($row)) {
			throw new \RuntimeException('Could not find row with uid "' . $uid . '" in table ' . $this->table, 1314354065);
		}
		return $this->createDomainObject($row);
	}
}