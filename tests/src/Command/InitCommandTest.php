<?php
namespace Catalyst\Tests\Command;

use Catalyst\Command\InitCommand;
use Catalyst\Service\CatalystService;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class InitCommandTest extends \PHPUnit\Framework\TestCase
{
    /** @var Application */
    private $application;

    protected function setUp(): void
    {
        $this->application = new Application();

        //$this->application->add(new InitCommand(new CatalystService()));
    }

    public function testExecute()
    {
        $this->markAsRisky();
        return true;
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
        $this->assertStringContainsString('Catalyst file initialized', $output);

        //@todo
        //unlink('catalyst.json');
    }
}
