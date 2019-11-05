<?php

namespace Catalyst\Tests\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Service\StorageService;
use Catalyst\Tests\TestHelper;
use Mockery\Exception;

class StorageServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var StorageService */
    private $subject;

    protected function setUp() : void
    {
        $this->subject = StorageService::getInstance();

        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
    }

    /**
     * @dataProvider provideResolvePaths
     */
    public function testResolvePaths($expected, $given)
    {
        $this->assertSame($expected, $this->subject->resolvePath($given));
    }

    public static function provideResolvePaths()
    {
        return [
            ['localfile.txt', 'localfile.txt'],
            ['local/file.txt', 'local/file.txt'],
            ['local/file.txt', 'local\\file.txt'],
            ['file.txt', 'local/../file.txt'],
            ['file.txt', 'local\\..\\file.txt'],
            ['C:/dir/file.txt', 'C:/dir/anotherdir/../file.txt'],
            ['C:/dir/file.txt', 'C:\\dir/anotherdir\\..\\file.txt'],
            ['/dir/file.txt', '/dir/anotherdir/../file.txt'],
            ['/dir/file.txt', '\\dir\\anotherdir\\..\\file.txt'],
            ['local/path/file.txt', 'local/path/path2/../file.txt'],
            ['local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
            ['local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
            ['local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
        ];
    }

    /**
     * @dataProvider provideResolvePathsException
     */
    public function testResolvePathsException($given)
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Climbing above the root is not permitted.');
        $this->subject->resolvePath($given);
    }

    public static function provideResolvePathsException()
    {
        return [
            ['localfile.txt/../../'],
            ['local/../../'],
            ['local\\..\\..\\file.txt'],
        ];
    }

    /**
     * @dataProvider proviceAbsoluteFilenames
     */
    public function testAbsoluteFilenames($expected, $given)
    {
        $this->assertSame($expected, $this->subject->getAbsoluteFilename($given));
    }

    public static function proviceAbsoluteFilenames()
    {
        $expectedRoot = str_replace('\\', '/', getcwd() . '/');
        return [
            [$expectedRoot . 'localfile.txt', 'localfile.txt'],
            [$expectedRoot . 'local/file.txt', 'local/file.txt'],
            [$expectedRoot . 'local/file.txt', 'local\\file.txt'],
            [$expectedRoot . 'file.txt', 'local/../file.txt'],
            [$expectedRoot . 'file.txt', 'local\\..\\file.txt'],
            [$expectedRoot . 'local/path/file.txt', 'local/path/path2/../file.txt'],
            [$expectedRoot . 'local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
            [$expectedRoot . 'local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
            [$expectedRoot . 'local/path/file.txt', 'local/path\\path2\\..\\file.txt'],
            [$expectedRoot . 'file.txt', 'local/path\\path2\\..\\..\\..\\file.txt'],
        ];
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
        $expectedFullPath = str_replace('\\','/', getcwd() . '/');
        $this->assertPrepareWriteFile($expectedFullPath . 'filename.json', '{content}');
    }

    public function testSaveEntity()
    {
        $this->markTestSkipped('not yet1');
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