<?php

namespace GMDepMan\Command;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RequireCommand extends Command
{
    protected static $defaultName = 'require';

    /** @var StorageService */
    private $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a dependency')
            ->setHelp('Add a dependency')
            ->addArgument('package', InputArgument::REQUIRED, 'GDM Package name or URL');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $depmanentity = new DepManEntity(realpath('.'));

        //var_dump($depmanentity->projectEntity()->getJson());

        $output->writeln('GMDepMan file loaded.');
    }
}