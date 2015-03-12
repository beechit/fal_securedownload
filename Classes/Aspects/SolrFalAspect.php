<?php
namespace BeechIt\FalSecuredownload\Aspects;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-03-2015 11:07
 * All code (c) Beech Applications B.V. all rights reserved
 */

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\Solr\Solrfal\Queue\Item;

/**
 * Class SolrFalAspect
 */
class SolrFalAspect implements SingletonInterface {

	/**
	 * @var CheckPermissions
	 */
	protected $checkPermissionsService;

	/**
	 * @var PublicUrlAspect
	 */
	protected $publicUrlAspect;

	/**
	 * Contructor
	 */
	public function __construct() {
		$this->checkPermissionsService = GeneralUtility::makeInstance(
			'BeechIt\\FalSecuredownload\\Security\\CheckPermissions'
		);
		$this->publicUrlAspect = GeneralUtility::makeInstance(
			'BeechIt\\FalSecuredownload\\Aspects\\PublicUrlAspect'
		);
	}

	/**
	 * Add correct fe_group info and public_url
	 *
	 * @param Item $item
	 * @param \ArrayObject $metadata
	 */
	public function fileMetaDataRetrieved(Item $item, \ArrayObject $metadata) {

		if ($item->getFile() instanceof File && !$item->getFile()->getStorage()->isPublic()) {
			$resourcePermissions = $this->checkPermissionsService->getPermissions($item->getFile());
			// If there are already permissions set, refine these with actual file permissions
			if ($metadata['fe_groups']) {
				$metadata['fe_groups'] = implode(',', GeneralUtility::keepItemsInArray(explode(',', $resourcePermissions), $metadata['fe_groups']));
			} else {
				$metadata['fe_groups'] = $resourcePermissions;
			}
		}

		// Re-generate public url
		$this->publicUrlAspect->setEnabled(FALSE);
		$metadata['public_url'] = $item->getFile()->getPublicUrl();
		$this->publicUrlAspect->setEnabled(TRUE);
	}
}