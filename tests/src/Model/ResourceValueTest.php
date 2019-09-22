<?php
namespace Catalyst\Tests\Model;

use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\GMFolder;
use Catalyst\Model\YoYo\ResourceValue;
use Catalyst\Service\JsonService;
use Catalyst\Tests\MockStorageTestCase;

class ResourceValueTest extends MockStorageTestCase
{
    private $jsonString = <<<EOL
{
    "id": "d4700bac-6283-45d1-899f-16cccf665418",
    "resourcePath": "views\\\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy",
    "resourceType": "GMFolder"
}
EOL;

    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockStorage
            ->shouldReceive('getJson')
            ->once()
            ->with('views\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy')
            ->andReturn(new \stdClass());

        $this->mockStorage
            ->shouldReceive('getContents')
            ->once()
            ->with('views\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy')
            ->andReturn('default_content');

        $this->subject = ResourceValue::createFromObject(JsonService::decode($this->jsonString));
    }

    public function testCreateFromGMResource()
    {
        $realContents = file_get_contents(__DIR__ . '/../../projects/GMLProject/views/0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy');
        $this->mockStorage
            ->shouldReceive('getJson')
            ->once()
            ->with('testfile.yy')
            ->andReturn(json_decode($realContents));

        $this->mockStorage
            ->shouldReceive('getContents')
            ->once()
            ->with('testfile.yy')
            ->andReturn($realContents);

        $uuid = '0bb36c74-dc11-4a76-8ff5-0d89046b21bf';
        $gmResource = GMFolder::createFromFile('testfile.yy');

        $newResource = ResourceValue::createFromGMResource($gmResource);

        $this->assertEquals((string) $uuid, (string) $newResource->id());
        $this->assertEquals($gmResource, $newResource->gmResource());
    }

    public function testGetJsonReturnsSameAdValuesCorrect()
    {
        $this->assertEquals(
            $this->jsonString,
            JsonService::encode($this->subject)
        );

        $this->assertEquals(
            JsonService::decode($this->jsonString),
            $this->subject->jsonSerialize()
        );
    }

    public function testValues()
    {
        $this->assertSame('d4700bac-6283-45d1-899f-16cccf665418', (string) $this->subject->id());
        $this->assertSame('GMFolder', (string) $this->subject->resourceType());
        $this->assertSame(
            'views\\0bb36c74-dc11-4a76-8ff5-0d89046b21bf.yy',
            (string) $this->subject->resourcePath()
        );
    }
}