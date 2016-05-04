<?php
namespace BeechIt\FalSecuredownload\Tests\Unit\Security;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 21-04-2016
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\FalSecuredownload\Security\CheckPermissions;
use TYPO3\CMS\Core\Tests\UnitTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CheckPermissionsTest
 */
class CheckPermissionsTest extends UnitTestCase
{
    /**
     * @test
     */
    public function createCheckPermissionsObjectTest()
    {
        $checkPermissions = new CheckPermissions();

        $this->assertInstanceOf(CheckPermissions::class, $checkPermissions);
    }

    /**
     * @test
     */
    public function getFolderRootLineGivesAllFoldersUpToStorageRoot()
    {
        $checkPermissions = new CheckPermissions();



//        $rootline = $checkPermissions->getFolderRootLine($folder);

    }

    /**
     * @return array
     */
    public function matchFeGroupsWithFeUserDataProvider()
    {
        return [
            'Not loggedin' => [
                '1',
                false,
                false
            ],
            'Loggedin' => [
                '-2',
                [1],
                true
            ]
        ];
    }

    /**
     * @test
     * @dataProvider matchFeGroupsWithFeUserDataProvider
     * @param string $groups
     * @param false|array $userFeGroups
     * @param bool $expectedResult
     */
    public function matchFeGroupsWithFeUserTest($groups, $userFeGroups, $expectedResult) {
        $checkPermissions = new CheckPermissions();

        $this->assertSame($expectedResult, $checkPermissions->matchFeGroupsWithFeUser($groups, $userFeGroups));
    }
}
