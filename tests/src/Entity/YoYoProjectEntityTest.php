<?php
namespace Catalyst\Tests\Entity;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Entity\OLDYoYoProjectEntity;
use Catalyst\Entity\YoYoProjectEntity;

class YoYoProjectEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testGetJsonReturnsSame()
    {
        $subject = YoYoProjectEntity::createFromFile(__DIR__ . '/../../projects/GMLProject/GMLProject.yyp');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../projects/GMLProject/GMLProject.yyp'),
            $subject->getJson()
        );
    }
}