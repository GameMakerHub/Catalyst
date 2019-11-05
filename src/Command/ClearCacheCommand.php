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
        $this->rrmdir($GLOBALS['SYMFONY_KERNEL']->getCacheDir());
    }

    private function rrmdir($path) {
        // @todo make this work with persist as well
        $i = new \DirectoryIterator($path);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rrmdir($f->getRealPath());
            }
        }
        rmdir($path);
    }

}