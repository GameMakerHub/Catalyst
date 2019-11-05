<?php

namespace Catalyst\Tests\Service;

use Catalyst\Service\CatalystService;
use Catalyst\Service\CleanService;
use Catalyst\Service\PackageService;
use Catalyst\Service\StorageService;
use Mockery\MockInterface;

class CleanServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var CleanService */
    private $subject;

    /** @var PackageService|MockInterface */
    private $packageService;

    /** @var StorageService|MockInterface */
    private $storageService;

    protected function setUp() : void
    {
        $this->storageService = \Mockery::mock(StorageService::class . '[persist]');
        StorageService::setInstance($this->storageService);
        $this->packageService = \Mockery::mock(PackageService::class);
        $this->subject = new CleanService($this->packageService);
    }

    public function testProjectClean()
    {
        // Load the actual file, then overwrite the storage service once files are going to be changed;
        $projectPath = __DIR__ . '/../../mocks/projects/21_UninstalledNewFiles';
        chdir($projectPath);
        $catalystProject = (new CatalystService())->load('.');

        // Actual testing
        $this->subject->clean($catalystProject);

        $this->assertCount(15, $GLOBALS['storage']['writes']);
        $this->assertCount(106, $GLOBALS['storage']['deletes']);
        $this->assertCount(0, $catalystProject->ignored());

        $filesThatShouldNotBeDeleted = [
            'objects/obj_test',
            'objects/obj_test/obj_test.yy',
            'scripts/newScript',
            'scripts/newScript/newScript.gml',
            'scripts/newScript/newScript.yy',
            'sprites/newSprite',
            'sprites/newSprite/newSprite.yy',
            'sprites/newSprite/e131be19-5848-45f2-b020-57f4410cf2a5.png',
            'sprites/newSprite/layers',
            'sprites/newSprite/layers/e131be19-5848-45f2-b020-57f4410cf2a5',
            'sprites/newSprite/layers/353bdc59-fc95-4c0e-b2e1-3c518555cc48/1fb31644-396c-40ab-95b2-00d06ba69873.png',
            'objects',
            'objects/obj_test',
            'objects/obj_test/obj_test.yy',
        ];

        $filesThatShouldBeDeleted = [
            'objects/ExtendedFunctions_obj_near_instance',
            'objects/ExtendedFunctions_obj_test',
            'scripts/extract_header_from_string',
            'scripts/hextodec/hextodec.gml',
            'scripts/hextodec/hextodec.yy',
        ];

        foreach ($filesThatShouldBeDeleted as $file) {
            $this->assertArrayHasKey(
                StorageService::pathToAbsolute($projectPath . '/' . $file),
                $GLOBALS['storage']['deletes']
            );
        }

        foreach ($filesThatShouldNotBeDeleted as $file) {
            $this->assertArrayNotHasKey(
                StorageService::pathToAbsolute($projectPath . '/' . $file),
                $GLOBALS['storage']['deletes']
            );
        }
    }

    protected function tearDown() : void {
        StorageService::reset();
    }

}