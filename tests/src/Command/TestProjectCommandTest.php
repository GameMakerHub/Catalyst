<?php
namespace GMM\Tests\Command;

use GMM\Command\TestProjectCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testExecute()
    {
        $application = new Application();
        $application->add(new TestProjectCommand());

        $command = $application->find('test-project');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command'  => $command->getName(),
            'username' => 'Rob',
            '--password' => 'secret',
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Username: Rob', $output);
        $this->assertContains('Password: secret', $output);
    }
}