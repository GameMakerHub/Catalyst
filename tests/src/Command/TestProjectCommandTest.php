<?php
namespace GMDepMan\Tests\Command;

use GMDepMan\Command\InitCommand;
use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $application;

    protected function setUp(): void
    {
        $this->application = new Application();
        $this->application->add(new InitCommand(new StorageService()));
    }

    public function testExecute()
    {
        $command = $this->application->find('test-project');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            'yyp' => 'yypfile',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('YYP: yypfile', $output);
    }
}