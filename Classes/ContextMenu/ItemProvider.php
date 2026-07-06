<?php

declare(strict_types=1);

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 11-1-19
 * All code (c) Beech Applications B.V. all rights reserved
 */

namespace BeechIt\FalSecuredownload\ContextMenu;

use BeechIt\FalSecuredownload\Service\Utility;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemProvider extends AbstractProvider
{
    protected ?Folder $folder = null;

    public function __construct(protected readonly ResourceFactory $resourceFactory)
    {
        parent::__construct();
    }

    public function getPriority(): int
    {
        return 90;
    }

    public function canHandle(): bool
    {
        return $this->table === 'sys_file' || $this->table === 'sys_file_storage';
    }

    /**
     * Initialize file object
     *
     * @throws ResourceDoesNotExistException
     */
    protected function initialize(): void
    {
        parent::initialize();
        $resource = $this->resourceFactory
            ->retrieveFileOrFolderObject($this->identifier);

        if ($resource instanceof Folder
            && !$resource->getStorage()->isPublic()
            && in_array(
                $resource->getRole(),
                [Folder::ROLE_DEFAULT, Folder::ROLE_USERUPLOAD],
                true
            )
        ) {
            $this->folder = $resource;
        }
    }

    /**
     * Adds the folder permission menu item for folder of a non-public storage
     *
     * @throws ResourceDoesNotExistException
     */
    public function addItems(array $items): array
    {
        $this->initialize();
        if ($this->folder instanceof Folder) {
            if ($this->backendUser->check('tables_modify', 'tx_falsecuredownload_folder')) {
                $items += $this->prepareItems([
                    'permissions_divider' => [
                        'type' => 'divider',
                    ],
                    'permissions' => [
                        'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:clickmenu.folderpermissions',
                        'iconIdentifier' => 'action-folder',
                        'callbackAction' => 'folderPermissions',
                    ],
                ]);
            }
        }

        return $items;
    }

    protected function getAdditionalAttributes(string $itemName): array
    {
        /** @var Utility $utility */
        $utility = GeneralUtility::makeInstance(Utility::class);
        $folderRecord = $utility->getFolderRecord($this->folder);

        return [
            'data-callback-module' => '@beechit/fal-securedownload/context-menu-actions',
            'data-folder-record-uid' => $folderRecord['uid'] ?? 0,
            'data-storage' => $this->folder->getStorage()->getUid(),
            'data-folder' => $this->folder->getIdentifier(),
            'data-folder-hash' => $this->folder->getHashedIdentifier(),
        ];
    }
}
