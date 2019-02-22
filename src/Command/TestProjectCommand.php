<?php

namespace GMDepMan\Command;

use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestProjectCommand extends Command
{
    protected static $defaultName = 'test-project';

    /** @var StorageService */
    private $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Test a project.')
            ->setHelp('Extended information about testing a project')
            ->addArgument(
                'yyp',
                InputArgument::REQUIRED,
                'The .yyp file of the project.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('YYP: '.$input->getArgument('yyp'));
    }
}