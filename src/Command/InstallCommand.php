<?php

namespace Catalyst\Command;

use Catalyst\Service\InstallService;
use Catalyst\Service\StorageService;
use Composer\Semver\Semver;
use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\UnresolveableDependenciesException;
use Catalyst\Service\CatalystService;
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
        $thisProject = $this->catalystService->load();

        $this->installService->setOutput($output);
        $this->installService->install($thisProject);

        $thisProject->save();

        StorageService::getInstance()->persist($input->getOption('dry-run'));
        //$thisProject->save();
        //$this->catalystService->uninstallAll();
        die;

        $thisDepMan = new CatalystEntity(realpath('.'));

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

    private function installDependencies(CatalystEntity $thisDepMan, OutputInterface $output) {
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