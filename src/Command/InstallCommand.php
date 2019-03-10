<?php

namespace GMDepMan\Command;

use Composer\Semver\Semver;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Exception\UnresolveableDependenciesException;
use GMDepMan\Service\PackageService;
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

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;

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
        $thisDepMan = new DepManEntity(realpath('.'));

        if ($input->getOption('dry-run')) {
            $GLOBALS['dry'] = true;
        } else {
            $GLOBALS['dry'] = false;
        }

        $this->solveDependencies($thisDepMan, $output, 0);

        //$requiredPackage = $this->packageService->getPackage($input->getArgument('package'));

        $this->installDependencies($thisDepMan, $output);

        //$output->writeln('Require version <fg=green>' . $requiredPackage->version() . '</> for <fg=green>' . $requiredPackage->name() . '</>');
    }

    private function solveDependencies(DepManEntity $depManEntity, OutputInterface $output, $indentLevel = 0)
    {
        foreach ($depManEntity->require as $package => $version) {
            $output->writeln(str_repeat('  ', $indentLevel) . 'Package <fg=green>' . $depManEntity->name() . '</>@<fg=cyan>' . $depManEntity->version() . '</> depends on <fg=yellow>' . $package . '</>@<fg=cyan>' . $version . '</>', Output::VERBOSITY_VERBOSE);

            if (!array_key_exists($package, $this->dependencies)) {
                // It doesn't exist yet, so search for versions we can use
                $this->dependencies[$package] = $this->packageService->getSatisfiableVersions($package, $version);
            } else {
                // It is already constrained, now we have to filter this version an extra time
                $satisfied = Semver::satisfiedBy($this->dependencies[$package], $version);
                if (count($satisfied)) {
                    $this->dependencies[$package] = $satisfied;
                } else {
                    throw new UnresolveableDependenciesException('Package ' . $package . ' could not be resolved because of conflicting dependency constraints.');
                }
            }

            $output->writeln(str_repeat('  ', $indentLevel+1) . 'satisfiable by <fg=cyan>' . implode(', ', $this->dependencies[$package]) . '</>', Output::VERBOSITY_VERBOSE);
            foreach ($this->dependencies[$package] as $testingVersion) {
                $this->solveDependencies($this->packageService->getPackage($package, $testingVersion), $output, $indentLevel+1);
            }
        }
    }

    private function installDependencies(DepManEntity $thisDepMan, OutputInterface $output) {
        foreach ($this->dependencies as $package => $versions) {
            $versionSort = Semver::rsort($versions);
            $output->writeln('Installing <fg=green>' . $package . '</>@<fg=cyan>' . $versionSort[0] . '</>', Output::VERBOSITY_VERBOSE);
            $output->writeln('    Candidates: <fg=cyan>' . implode(', ', $versions) . '</>', Output::VERBOSITY_VERBOSE);

            //$this->packageService->downloadPackage($package, $versionSort[0]); // Skip because we're local only now

            $newPackage = $this->packageService->getPackage($package, $versionSort[0]);

            // Make the vendor folder for this package
            $thisDepMan->installPackage($newPackage, $output);
        }
    }
}