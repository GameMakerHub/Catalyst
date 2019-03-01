<?php

namespace GMDepMan\Command;

use GMDepMan\Entity\DepManEntity;
use GMDepMan\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TreeCommand extends Command
{
    protected static $defaultName = 'tree';

    protected function configure()
    {
        $this
            ->setDescription('List the project as a tree')
            ->setHelp('List project assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $depmanentity = new DepManEntity(realpath('.'));

        $project = $depmanentity->projectEntity();

        $output->writeln('<fg=green>-</> ROOT');
        $this->loopIn($output, $project->getChildren(), 0);
    }

    private function loopIn(OutputInterface $output, array $children, $level = 0) {
        foreach ($children as $child) {
            $name = '?';
            if (isset($child->folderName)) {
                $name = $child->folderName;
            } else if (isset($child->name)) {
                $name = $child->name;
            }

            $output->writeln('<fg=green>' . str_repeat('|  ', $level).'\__</> ' . $name);
            if (count($child->getChildren())) {
                $this->loopIn($output, $child->getChildren(), $level+1);
            }
        }
    }
}