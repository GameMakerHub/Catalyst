<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Symfony\Component\Console\Output\OutputInterface;

class InstallService
{
    /** @var OutputInterface */
    private $output;

    /** @var PackageService */
    private $packageService;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function install(CatalystEntity $project)
    {
        $packagesToInstall = $this->packageService->solveDependencies($project);

        foreach ($packagesToInstall as $package => $version) {
            $this->writeLine('Installing <fg=green>' . $package . '</>@<fg=cyan>' . $version . '</>...');

            $package = $this->packageService->getPackage($package, $version);
        }
    }

    private function writeLine($string)
    {
        if (null !== $this->output) {
            $this->output->writeln($string);
        }
    }
}