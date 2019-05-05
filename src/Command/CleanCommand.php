<?php

namespace Catalyst\Command;

use Catalyst\Service\CatalystService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    protected static $defaultName = 'clean';

    /** @var CatalystService */
    private $catalystService;

    public function __construct(CatalystService $catalystService)
    {
        $this->catalystService = $catalystService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes all dependencies')
            ->setHelp('Remove all dependencies, files and folders installed by Catalyst');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $GLOBALS['dry'] = false;
        $this->catalystService->uninstallAll();
    }

}