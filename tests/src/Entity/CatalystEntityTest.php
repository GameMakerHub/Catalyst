<?php
namespace Catalyst\Tests\Entity;

use Catalyst\Tests\TestHelper;

class CatalystEntityTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNew()
    {
        $this->markTestSkipped();
        return true;
        $subject = TestHelper::getTestCatalystEntity();

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