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

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FlexForm file mount service
 */
class UserFileMountService extends \TYPO3\CMS\Core\Resource\Service\UserFileMountService
{

    /**
     * User function for to render a dropdown for selecting a folder
     * of a selected storage
     *
     * @param array $PA the array with additional configuration options.
     * @throws \TYPO3\CMS\Core\Exception
     */
    public function renderFlexFormSelectDropdown(&$PA)
    {
        // get storageUid from flexform
        $storageUid = $PA['row']['settings.storage'][0];

        // if storageUid found get folders
        if ($storageUid > 0) {
            // reset items
            $PA['items'] = [];

            /** @var $storageRepository StorageRepository */
            $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
            /** @var $storage \TYPO3\CMS\Core\Resource\ResourceStorage */
            $storage = $storageRepository->findByUid($storageUid);
            if ($storage->isBrowsable()) {
                $rootLevelFolder = $storage->getRootLevelFolder();
                $folderItems = $this->getSubfoldersForOptionList($rootLevelFolder);
                foreach ($folderItems as $item) {
                    $PA['items'][] = [
                        $item->getIdentifier(),
                        $item->getIdentifier()
                    ];
                }
            } else {
                /** @var FlashMessageService $flashMessageService */
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $queue = $flashMessageService->getMessageQueueByIdentifier();
                $queue->enqueue(new FlashMessage(
                    'Storage "' . $storage->getName() . '" is not browsable. No folder is currently selectable.',
                    '',
                    FlashMessage::WARNING
                ));

                if (!count($PA['items'])) {
                    $PA['items'][] = [
                        '',
                        ''
                    ];
                }
            }
        }
    }
}
