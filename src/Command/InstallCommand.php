<?php

namespace GMDepMan\Command;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Service\PackageService;
use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected static $defaultName = 'install';

    /** @var PackageService */
    private $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Install all dependencies')
            ->setHelp('Solves the dependencies and tries to install all dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $thisDepMan = new DepManEntity(realpath('.'));

        foreach ($thisDepMan->require as $package => $version) {
            $output->writeln('Checking <fg=green>' . $package . '</> at <fg=green>' . $version . '</>');
            $this->packageService->getPackage($package);
        }

        $requiredPackage = $this->packageService->getPackage($input->getArgument('package'));

        $output->writeln('Require version <fg=green>' . $requiredPackage->version() . '</> for <fg=green>' . $requiredPackage->name() . '</>');

        if ($thisDepMan->hasPackage($requiredPackage->name())) {
            $output->writeln('<bg=red>' . $requiredPackage->name() . ' is already required</>');
            return 1;
        }

        $thisDepMan->require($requiredPackage);

        $thisDepMan->save();
        $output->writeln('<fg=green>gmdepman.json has been updated</>');
    }
}