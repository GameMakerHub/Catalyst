<?php

namespace Catalyst\Command;

use Composer\Semver\Semver;
use Catalyst\Entity\DepManEntity;
use Catalyst\Exception\UnresolveableDependenciesException;
use Catalyst\Service\DepmanService;
use Catalyst\Service\PackageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /** @var array */
    private $dependencies = [];

    protected static $defaultName = 'install';

    /** @var PackageService */
    private $packageService;

    /** @var DepmanService */
    private $depmanService;

    public function __construct(PackageService $packageService, DepmanService $depmanService)
    {
        $this->packageService = $packageService;
        $this->depmanService = $depmanService;

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
        if ($input->getOption('dry-run')) {
            $GLOBALS['dry'] = true;
        } else {
            $GLOBALS['dry'] = false;
        }

        $output->writeln('Uninstalling current packages...', Output::VERBOSITY_VERBOSE);
        $this->depmanService->uninstallAll();

        $thisDepMan = new DepManEntity(realpath('.'));

        $output->writeln('Package <fg=green>' . $thisDepMan->name() . '</>@<fg=cyan>' . $thisDepMan->version() . '</> depends on:', Output::VERBOSITY_VERBOSE);
        $this->solveDependencies($thisDepMan->require, $output, 0);

        //$requiredPackage = $this->packageService->getPackage($input->getArgument('package'));



        $this->installDependencies($thisDepMan, $output);

        //$output->writeln('Require version <fg=green>' . $requiredPackage->version() . '</> for <fg=green>' . $requiredPackage->name() . '</>');
    }

    private function solveDependencies(array $requiredPackages, OutputInterface $output, $indentLevel = 0)
    {
        foreach ($requiredPackages as $package => $version) {
            $output->writeln(str_repeat('  ', $indentLevel) .'<fg=yellow>' . $package . '</>@<fg=cyan>' . $version . '</>', Output::VERBOSITY_VERBOSE);

            if (!array_key_exists($package, $this->dependencies)) {
                // It doesn't exist yet, so search for versions we can use
                $this->dependencies[$package] = $this->packageService->getSatisfiableVersions($package, $version);
            }

            // Filter all that are satisfied
            $satisfied = Semver::satisfiedBy($this->dependencies[$package], $version);
            if (count($satisfied)) {
                $this->dependencies[$package] = $satisfied;
            } else {
                throw new UnresolveableDependenciesException('Package ' . $package . ' could not be resolved because of the dependency constraints.');
            }

            $output->writeln(str_repeat('  ', $indentLevel+1) . 'satisfiable by <fg=cyan>' . implode(', ', $this->dependencies[$package]) . '</>', Output::VERBOSITY_VERBOSE);
            foreach ($this->dependencies[$package] as $testingVersion) {
                // Gep dependencies for testversion
                $output->writeln(str_repeat('  ', $indentLevel+1) . 'Package <fg=green>' . $package . '</>@<fg=cyan>' . $testingVersion . '</> depends on:', Output::VERBOSITY_VERBOSE);
                $this->solveDependencies($this->packageService->getPackageDependencies($package, $testingVersion), $output, $indentLevel+2);
            }
        }
    }

    private function installDependencies(DepManEntity $thisDepMan, OutputInterface $output) {
        foreach ($this->dependencies as $package => $versions) {
            $versionSort = Semver::rsort($versions);
            $output->writeln('Installing <fg=green>' . $package . '</>@<fg=cyan>' . $versionSort[0] . '</>');
            //$output->writeln('    Candidates: <fg=cyan>' . implode(', ', $versions) . '</>', Output::VERBOSITY_VERBOSE);

            //$this->packageService->downloadPackage($package, $versionSort[0]); // Skip because we're local only now

            $newPackage = $this->packageService->getPackage($package, $versionSort[0]);

            // Make the vendor folder for this package
            $thisDepMan->installPackage($newPackage, $output);
        }
    }
}