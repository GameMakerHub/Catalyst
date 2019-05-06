<?php
namespace Catalyst\Tests\Entity;

use Catalyst\Tests\TestHelper;

class CatalystEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNew()
    {
        $subject = TestHelper::getTestCatalystEntity();

        // assert that your calculator added the numbers correctly!
        $this->assertSame(realpath('.'), $subject->path());

        $this->assertSame('project/name', $subject->name());
        $this->assertSame('A description for the project', $subject->description());
        $this->assertSame('MIT', $subject->license());
        $this->assertSame('https://github.com/my/repository', $subject->homepage());
        $this->assertSame('somefile.yyp', $subject->yyp());

        $this->assertSame([], $subject->require());
        $this->assertSame([], $subject->repositories());
    }
}