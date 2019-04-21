<?php

namespace GMDepMan\Command;

use GMDepMan\Service\DepmanService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    protected static $defaultName = 'clean';

    /** @var DepmanService */
    private $depmanService;

    public function __construct(DepmanService $depmanService)
    {
        $this->depmanService = $depmanService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes all dependencies')
            ->setHelp('Remove all dependencies, files and folders installed by GMDepMan');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $GLOBALS['dry'] = false;
        $this->depmanService->uninstallAll();
    }

}