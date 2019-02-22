<?php

namespace GMM\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestProjectCommand extends Command
{
    protected static $defaultName = 'test-project';

    protected function configure()
    {
        $this->setDescription('Test a project.')
            ->setHelp('Extended information about testing a project')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The username of the user.'
            )->addOption(
                'password',
                'p',
                InputArgument::OPTIONAL,
                'User password'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Username: '.$input->getArgument('username'));
        $output->writeln('Password: '.$input->getOption('password'));
    }
    
    public function thisIsNotTested(InputInterface $input, OutputInterface $output)
    {
        $a = 430*439;
        $output->writeln('Password: '.$input->getOption('password'));
    }
}
