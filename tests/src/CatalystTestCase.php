<?php
namespace Catalyst\Tests;

use Catalyst\Service\StorageService;
use Mockery\MockInterface;

class MockStorageTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var StorageService|MockInterface */
    protected $mockStorage;

    protected function setUp(): void
    {
        $this->mockStorage = \Mockery::mock(StorageService::class);
        StorageService::setInstance($this->mockStorage);
    }

    protected function tearDown(): void
    {
        StorageService::setInstance(null); //Reset!
    }
}