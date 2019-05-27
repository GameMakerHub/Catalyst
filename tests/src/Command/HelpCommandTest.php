<?php
namespace Catalyst\Tests\Command;

use Catalyst\Command\HelpCommand;
use Symfony\Component\Console\Tester\CommandTester;

class HelpCommandTest extends CommandTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->initCommand(new HelpCommand());
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
