<?php

namespace Catalyst\Command;

use Catalyst\Service\CatalystService;
use Catalyst\Service\InstallService;
use Catalyst\Service\PackageService;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';

    /** @var PackageService */
    private $packageService;

    /** @var CatalystService */
    private $catalystService;

    /** @var InstallService */
    private $installService;

    public function __construct(
        PackageService $packageService,
        CatalystService $catalystService,
        InstallService $installService
    ) {
        $this->packageService = $packageService;
        $this->catalystService = $catalystService;
        $this->installService = $installService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Install all dependencies')
            ->setHelp('Solves the dependencies and tries to install all dependencies')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'Don\'t touch any files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=green>Loading project...</>');
        $thisProject = $this->catalystService->load();

        // @todo first uninstall all vendored files and folders so we can install a clean version.

        $this->installService->setOutput($output);
        $this->installService->install($thisProject);

        $thisProject->save();

        StorageService::getInstance()->persist($input->getOption('dry-run'));
    }
}