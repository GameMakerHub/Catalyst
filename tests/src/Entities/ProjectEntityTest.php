<?php
namespace GMM\Tests\Entities;

use GMM\Entities\ProjectEntity;

class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    public function testValue()
    {
        $projectEntity = new ProjectEntity();

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(124, $projectEntity->testValue());
    }
}