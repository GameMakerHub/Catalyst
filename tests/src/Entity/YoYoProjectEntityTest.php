<?php
namespace Catalyst\Tests\Entity;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Entity\OLDYoYoProjectEntity;

class YoYoProjectEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testLoadingProjectAndSavingJsonUntouched()
    {
        $this->markAsRisky();
        /*
        $depManEntity = (new CatalystEntity('./tests/projects/GMLProject'));

        $projectEntity = new YoYoProjectEntity();
        $projectEntity->load($depManEntity);

        $json = file_get_contents('./tests/projects/GMLProject/GMLProject.yyp');

        // assert that your calculator added the numbers correctly!
        $this->assertJsonStringEqualsJsonString($json, $projectEntity->getJson());
        */
    }
}