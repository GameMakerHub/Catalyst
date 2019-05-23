<?php
namespace Catalyst\Tests\Entity;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Entity\OLDYoYoProjectEntity;
use Catalyst\Entity\YoYoProjectEntity;

class YoYoProjectEntityTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        // Set our working dir
        chdir(__DIR__ . '/../../projects/GMLProject/');
    }

    public function testGetJsonReturnsSame()
    {
        $subject = YoYoProjectEntity::createFromFile('GMLProject.yyp');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/../../projects/GMLProject/GMLProject.yyp'),
            $subject->getJson()
        );
    }
}