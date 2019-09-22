<?php

namespace Catalyst\Tests\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Service\JsonService;

class JsonServiceTest extends \PHPUnit\Framework\TestCase
{
    public function testDecode()
    {
        $testClass = new \stdClass();
        $testClass->value = 'some string';
        $testClass->boolean = true;
        $testClass->array = ['item 1', 'item 2', true, 1234];

        $this->assertEquals(
            JsonService::decode(json_encode($testClass)),
            $testClass
        );
    }

    public function testGetBrokenJson()
    {
        $this->expectException(MalformedJsonException::class);
        JsonService::decode('borked json');
    }

    public function testGetBrokenJsonWithJustCommas()
    {
        $this->expectException(MalformedJsonException::class);
        JsonService::decode('{"test":["data", "data2",]}');
    }

    /**
     * @dataProvider provideEncode
     */
    public function testEncode($string)
    {
        $this->assertSame(
            $string,
            JsonService::encode(JsonService::decode($string))
        );
    }

    public function provideEncode()
    {
        return [
            [file_get_contents(__DIR__ . '/../../projects/GMLProject/GMLProject.yyp')],
            [file_get_contents(__DIR__ . '/../../projects/OtherProject/OtherProject.yyp')],
            [file_get_contents(__DIR__ . '/../../projects/SimpleProject/SimpleProject.yyp')],
        ];
    }
}