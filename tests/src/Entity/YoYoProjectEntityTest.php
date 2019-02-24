<?php
namespace GMDepMan\Tests\Entity;

use GMDepMan\Entity\YoYoProjectEntity;

class YoYoProjectEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testValue()
    {
        $projectEntity = new YoYoProjectEntity();

        $json = file_get_contents('./tests/projects/GMLProject/GMLProject.yyp');

        $projectEntity->load($json);

        // assert that your calculator added the numbers correctly!
        $this->assertJsonStringEqualsJsonString($json, $projectEntity->getJson());
    }
}