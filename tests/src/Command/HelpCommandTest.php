<?php
namespace Catalyst\Tests\Command;

use Catalyst\Command\HelpCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class HelpCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $application;

    protected function setUp(): void
    {
        $this->application = new Application();
        $this->application->add(new HelpCommand());
    }

    public function testExecute()
    {
        $command = $this->application->find('help');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command'  => $command->getName()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('command displays help for a given command', $output);
        $this->assertStringNotContainsString(' php ', $output);
    }
}
