<?php

declare(strict_types=1);

namespace BeechIt\FalSecuredownload\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('beechitFalSecuredownloadCTypeMigration')]
final class BeechItFalSecuredownloadCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate "BeechIt FalSecuredownload" plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The "BeechIt FalSecuredownload" plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * This must return an array containing the "list_type" to "CType" mapping
     *
     *  Example:
     *
     *  [
     *      'pi_plugin1' => 'pi_plugin1',
     *      'pi_plugin2' => 'new_content_element',
     *  ]
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'falsecuredownload_filetree' => 'falsecuredownload_filetree'
        ];
    }
}
