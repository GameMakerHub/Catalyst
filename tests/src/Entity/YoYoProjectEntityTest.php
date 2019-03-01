<?php
namespace GMDepMan\Tests\Entity;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Entity\YoYoProjectEntity;

class YoYoProjectEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testLoadingProjectAndSavingJsonUntouched()
    {
        $depManEntity = (new DepManEntity('./tests/projects/GMLProject'));

        $projectEntity = new YoYoProjectEntity();
        $projectEntity->load($depManEntity);

        $json = file_get_contents('./tests/projects/GMLProject/GMLProject.yyp');

        // assert that your calculator added the numbers correctly!
        $this->assertJsonStringEqualsJsonString($json, $projectEntity->getJson());
    }
}