<?php

namespace Catalyst\Tests\Service;

use Catalyst\Entity\CatalystEntity;
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
        $this->packageService = \Mockery::mock(PackageService::class);
        $this->subject = new CleanService($this->packageService);
    }

    public function testProjectClean()
    {
        // Load the actual file, then overwrite the storage service once files are going to be changed;
        $catalystProject = (new CatalystService())->load(__DIR__ . '/../../mocks/projects/21_UninstalledNewFiles');
        StorageService::setInstance($this->storageService);

        $this->assertCount(0, $GLOBALS['storage']['writes']);
        $this->assertCount(0, $GLOBALS['storage']['deletes']);
        $this->assertCount(0, $catalystProject->ignored());

        // Actual testing
        $this->subject->clean($catalystProject);

        $this->assertCount(15, $GLOBALS['storage']['writes']);
        $this->assertCount(0, $GLOBALS['storage']['deletes']);
        $this->assertCount(0, $catalystProject->ignored());
    }

    protected function tearDown() : void {
        StorageService::reset();
    }

}