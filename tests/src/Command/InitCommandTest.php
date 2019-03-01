<?php
namespace GMDepMan\Tests\Command;

use GMDepMan\Command\InitCommand;
use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends \PHPUnit\Framework\TestCase
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
        $command = $this->application->find('init');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName()
        ]);

        $commandTester->setInputs([
            'test/test',
            ''
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('YYP: yypfile', $output);
    }
}