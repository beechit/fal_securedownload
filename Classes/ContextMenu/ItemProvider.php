<?php

namespace BeechIt\FalSecuredownload\ContextMenu;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 11-1-19
 * All code (c) Beech Applications B.V. all rights reserved
 */

use BeechIt\FalSecuredownload\Service\Utility;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\AbstractProvider;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemProvider extends AbstractProvider
{

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * ItemProvider constructor.
     * @param ResourceFactory|null $resourceFactory
     */
    public function __construct(string $table, string $identifier, string $context = '', ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
        parent::__construct($table, $identifier, $context);
    }

    /**
     * @var Folder
     */
    protected $folder;

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
     */
    protected function initialize()
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
     */
    public function addItems(array $items): array
    {
        $this->initialize();
        if ($this->folder instanceof Folder) {
            $items += $this->prepareItems([
                'permissions_divider' => [
                    'type' => 'divider',
                ],
                'permissions' => [
                    'label' => 'LLL:EXT:fal_securedownload/Resources/Private/Language/locallang_be.xlf:clickmenu.folderpermissions',
                    'iconIdentifier' => 'action-folder',
                    'callbackAction' => 'folderPermissions'
                ]
            ]);
        }

        return $items;
    }

    protected function getAdditionalAttributes(string $itemName): array
    {
        /** @var Utility $utility */
        $utility = GeneralUtility::makeInstance(Utility::class);
        $folderRecord = $utility->getFolderRecord($this->folder);

        return [
            'data-callback-module' => 'TYPO3/CMS/FalSecuredownload/ContextMenuActions',
            'data-folder-record-uid' => $folderRecord['uid'] ?? 0,
            'data-storage' => $this->folder->getStorage()->getUid(),
            'data-folder' => $this->folder->getIdentifier(),
            'data-folder-hash' => $this->folder->getHashedIdentifier(),
        ];
    }
}
