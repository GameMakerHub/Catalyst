<?php

namespace Catalyst\Tests\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Service\StorageService;
use Catalyst\Tests\TestHelper;

class StorageServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var StorageService */
    private $subject;

    protected function setUp() : void
    {
        $this->subject = new StorageService();

        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
    }

    public function testGlobals()
    {
        $this->assertArrayHasKey('storage', $GLOBALS);
        $this->assertArrayHasKey('writes', $GLOBALS['storage']);
    }

    public function testFileExists() {
        $this->assertTrue($this->subject->fileExists(__FILE__));
        $this->assertFalse($this->subject->fileExists(__FILE__ . '/../'));
    }

    public function testWriteFile()
    {
        $this->subject->writeFile('filename.json', '{content}');
        $this->assertPrepareWriteFile('filename.json', '{content}');
    }

    public function testSaveEntity()
    {
        $testEntity = TestHelper::getTestCatalystEntity();
        $expectedOutput = TestHelper::getMockFile('catalyst/empty.json');
        $this->subject->saveEntity($testEntity);

        $this->assertPrepareWriteFile(realpath('.') . '/catalyst.json', $expectedOutput);
    }

    public function testGetContents()
    {
        $this->assertSame(file_get_contents(__FILE__), $this->subject->getContents(__FILE__));
    }

    public function testGetJson()
    {
        $this->assertEquals(
            json_decode(TestHelper::getMockFile('catalyst/empty.json')),
            $this->subject->getJson(__DIR__ . '/../../mocks/catalyst/empty.json')
        );
    }

    public function testGetBrokenJson()
    {
        $this->expectException(MalformedJsonException::class);
        $this->subject->getJson(__FILE__);
    }

    public function testPersist()
    {
        $this->markTestIncomplete('TODO');
    }

    private function assertPrepareWriteFile(string $filename, string $contents)
    {
        $this->assertArrayHasKey('storage', $GLOBALS);
        $this->assertArrayHasKey('writes', $GLOBALS['storage']);
        $this->assertArrayHasKey($filename, $GLOBALS['storage']['writes']);
        $this->assertSame($contents, $GLOBALS['storage']['writes'][$filename]);
    }
}