<?php

namespace Catalyst\Command;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Service\PackageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequireCommand extends Command
{
    protected static $defaultName = 'require';

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
            ->setDescription('Require a dependency (vendor/package@version or vendor/package)')
            ->setHelp('Adds a package as dependency to the current project')
            ->addArgument('package', InputArgument::REQUIRED, 'GDM Package name or URI');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $GLOBALS['dry'] = false;
        $thisDepMan = new CatalystEntity(realpath('.'));

        $version = '*';
        preg_match('~^([a-z0-9-]+\/[a-z0-9-]+)(\@[a-z0-9.\-\*\^\>\=\<]+)?$~', $input->getArgument('package'), $matches);
        if (!isset($matches[1])) {
            $output->writeln('<bg=red>Invalid or missing package name (format must be vendor/package or vendor/package@version)</>');
            return 1;
        }

        $package = $matches[1];
        if (isset($matches[2])) {
            $version = substr($matches[2], 1, strlen($matches[2])-1);
        }

        if ($package == $thisDepMan->name()) {
            $output->writeln('<bg=red>Package can not require itself</>');
            return 1;
        }

        $output->writeln('Require version <fg=green>' . $version . '</> for <fg=green>' . $package . '</>');

        if ($thisDepMan->hasPackage($package)) {
            $output->writeln('<bg=red>' . $package . ' is already required</>');
            return 1;
        }

        $this->packageService->getPackage($package, $version);

        $thisDepMan->require($package, $version);

        $thisDepMan->save();
        $output->writeln('<fg=green>catalyst.json has been updated</>');
    }
}