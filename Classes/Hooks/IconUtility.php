<?php
namespace BeechIt\FalSecuredownload\Hooks;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 22-02-2014 19:46
 * All code (c) Beech Applications B.V. all rights reserved
 */
class IconUtility {

	/**
	 * @param \TYPO3\CMS\Core\Resource\ResourceInterface $resource
	 * @param $iconName
	 * @param array $options
	 * @param array $overlays
	 */
	public function overrideResourceIcon(\TYPO3\CMS\Core\Resource\ResourceInterface $resource, $iconName, array $options, array &$overlays) {
		if (!$resource->getStorage()->isPublic()) {
			$overlays['status-overlay-access-restricted'] = array();
		}
	}

}

?>