<?php

namespace Catalyst\Command;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Exception\PackageNotFoundException;
use Catalyst\Service\CatalystService;
use Catalyst\Service\PackageService;
use Catalyst\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequireCommand extends Command
{
    protected static $defaultName = 'require';

    /** @var PackageService */
    private $packageService;

    /** @var CatalystService */
    private $catalystService;

    public function __construct(PackageService $packageService, CatalystService $catalystService)
    {
        $this->packageService = $packageService;
        $this->catalystService = $catalystService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Require a dependency (vendor/package@version or vendor/package)')
            ->setHelp('Adds a package as dependency to the current project')
            ->addArgument('package', InputArgument::REQUIRED, 'GDM Package name or URI');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $catalyst = $this->catalystService->load(realpath('.'));
        } catch (\Exception $e) {
            $output->writeLn('<fg=red>ERROR LOADING CATALYST PROJECT FILE: </>' . $e->getMessage());
            return 127;
        }

        $version = '*';
        preg_match('~^([a-z0-9-_]+\/[a-z0-9-_]+)(\@[a-z0-9.\-\*\^\>\=\<]+)?$~', $input->getArgument('package'), $matches);
        if (!isset($matches[1])) {
            $output->writeln('<bg=red>Invalid or missing package name (format must be vendor/package or vendor/package@version)</>');
            return 1;
        }

        $package = $matches[1];
        if (isset($matches[2])) {
            $version = substr($matches[2], 1, strlen($matches[2])-1);
        }

        if ($package == $catalyst->name()) {
            $output->writeln('<bg=red>Package can not require itself</>');
            return 1;
        }

        $output->writeln('Require version <fg=green>' . $version . '</> for <fg=green>' . $package . '</>');

        if ($catalyst->hasPackage($package)) {
            $output->writeln('<bg=red>' . $package . ' is already required</>');
            return 1;
        }

        if (!$this->packageService->packageExists($package, $version)) {
            throw new PackageNotFoundException($package, $version);
        }

        $catalyst->addRequire($package, $version);

        StorageService::getInstance()->saveEntity($catalyst);
        StorageService::getInstance()->persist();

        $output->writeln('<fg=green>catalyst.json has been updated</>');

        return 0;
    }
}