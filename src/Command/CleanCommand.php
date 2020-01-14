<?php

namespace Catalyst\Command;

use Catalyst\Service\CatalystService;
use Catalyst\Service\CleanService;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    protected static $defaultName = 'clean';

    /** @var CatalystService */
    private $catalystService;

    /** @var CleanService */
    private $cleanService;


    public function __construct(CatalystService $catalystService, CleanService $cleanService)
    {
        $this->catalystService = $catalystService;
        $this->cleanService = $cleanService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes all dependencies')
            ->setHelp('Remove all dependencies, files and folders installed by Catalyst')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don\'t touch any files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=green>Loading project...</>');
        $thisProject = $this->catalystService->load();

        $this->cleanService->setOutput($output);
        $this->cleanService->clean($thisProject);

        $thisProject->save();

        StorageService::getInstance()->persist($input->getOption('dry-run'));

        return 0;
    }

}