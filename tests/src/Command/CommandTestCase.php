<?php
namespace Catalyst\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

abstract class CommandTestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    protected $application;

    protected function setUp(): void
    {
        $this->application = new Application();
    }

    protected function initCommand(Command $command)
    {
        $this->application->add($command);
    }
}
