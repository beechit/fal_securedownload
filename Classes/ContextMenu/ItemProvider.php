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
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ItemProvider extends AbstractProvider
{

    protected ResourceFactory $resourceFactory;
    protected ?Folder $folder = null;

    /**
     * Constructor arguments are only needed for TYPO3 v11
     * @see https://docs.typo3.org/c/typo3/cms-core/12.4/en-us/Changelog/12.0/Breaking-96333-AutoConfigurationOfContextMenuItemProviders.html
     *
     * @param string $table
     * @param string $identifier
     * @param string $context
     * @param ResourceFactory|null $resourceFactory
     */
    public function __construct(string $table, string $identifier, string $context = '', ?ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
        parent::__construct($table, $identifier, $context);
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

        $typo3Version = new Typo3Version();
        if ($typo3Version->getMajorVersion() > 11) {
            $dataCallbackModule = '@beechit/fal-securedownload/context-menu-actions';
        } else {
            // keep RequireJs for TYPO3 below v12.0
            $dataCallbackModule = 'TYPO3/CMS/FalSecuredownload/ContextMenuActions';
        }

        return [
            'data-callback-module' => $dataCallbackModule,
            'data-folder-record-uid' => $folderRecord['uid'] ?? 0,
            'data-storage' => $this->folder->getStorage()->getUid(),
            'data-folder' => $this->folder->getIdentifier(),
            'data-folder-hash' => $this->folder->getHashedIdentifier(),
        ];
    }
}
