<?php

namespace Catalyst\Command;

use Catalyst\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCacheCommand extends Command
{
    protected static $defaultName = 'clear-cache';

    protected function configure()
    {
        $this
            ->setDescription('Clear all cache files that Catalyst generated')
            ->setHelp('Clear all cache files that Catalyst generated. This includes cached and downloaded packages!');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //@todo do a double check with the user?
        $output->writeln('Clearing cache at <fg=green>' . $GLOBALS['SYMFONY_KERNEL']->getCacheDir() . '</>');
        StorageService::getInstance()->rrmdir($GLOBALS['SYMFONY_KERNEL']->getCacheDir());
    }

}