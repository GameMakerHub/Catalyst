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
        //$this->storageService = new Mockery
        $this->application->add(new InitCommand(new StorageService()));
    }

    public function testExecute()
    {
        $command = $this->application->find('init');
        $commandTester = new CommandTester($command);

        $commandTester->setInputs([
            'test/test',
            'description',
            0,
            'http://www.dukesoft.nl/',
            __DIR__ . '/../../projects/GMLProject/GMLProject.yyp'
        ]);

        $commandTester->execute([
            'command'  => $command->getName()
        ]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('GMDepMan file initialized', $output);

        //@todo
        unlink('gmdepman.gdm');
        unlink('gmdepman.json');
    }
}
