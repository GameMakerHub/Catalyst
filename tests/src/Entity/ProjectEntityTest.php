<?php
namespace GMDepMan\Tests\Entity;

use GMDepMan\Entity\ProjectEntity;

class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValue()
    {
        $projectEntity = new ProjectEntity();

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(123, $projectEntity->testValue());
    }
}