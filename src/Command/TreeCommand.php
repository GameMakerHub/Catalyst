<?php

namespace Catalyst\Command;

use Catalyst\Entity\CatalystEntity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TreeCommand extends Command
{
    protected static $defaultName = 'tree';

    protected function configure()
    {
        $this
            ->setDescription('List the project as a tree')
            ->setHelp('List project assets')
            ->addOption(
                'show-all',
                'a',
                InputOption::VALUE_NONE,
                'Also list empty root folders'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $depmanentity = new CatalystEntity(realpath('.'));

        $project = $depmanentity->projectEntity();

        $output->writeln('<fg=green>-</> ROOT');
        $this->loopIn($input, $output, $project->getChildren(), 0);
    }

    private function loopIn(InputInterface $input, OutputInterface $output, array $children, $level = 0) {
        foreach ($children as $child) {
            $name = '?';
            if (isset($child->folderName)) {
                $name = $child->folderName;
            } else if (isset($child->name)) {
                $name = $child->name;
            }
            $hasChildren = count($child->getChildren()) >= 1;
            if ($level > 0 || ($hasChildren || $input->getOption('show-all'))) {
                $output->writeln('<fg=green>' . str_repeat('|  ', $level).'\__</> ' . $name);
            }

            if ($hasChildren) {
                $this->loopIn($input, $output, $child->getChildren(), $level+1);
            }
        }
    }
}