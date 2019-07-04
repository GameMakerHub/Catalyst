<?php

namespace Catalyst\Command;

use Catalyst\Model\YoYo\Resource\GM\GMResource;
use Catalyst\Service\CatalystService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TreeCommand extends Command
{
    protected static $defaultName = 'tree';

    /** @var CatalystService */
    protected $catalystService;

    public function __construct(CatalystService $catalystService)
    {
        $this->catalystService = $catalystService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('List the project as a tree')
            ->setHelp('List project assets')
            ->addOption(
                'id',
                'i',
                InputOption::VALUE_NONE,
                'Show GUID of resource'
            )->addOption(
                'type',
                't',
                InputOption::VALUE_NONE,
                'Show type of resource'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $catalyst = $this->catalystService->load(realpath('.'));
        } catch (\Exception $e) {
            $output->writeLn('<fg=red>ERROR LOADING CATALYST PROJECT FILE: </>' . $e->getMessage());
            return 127;
        }

        $showId = $input->getOption('id');
        $showType = $input->getOption('type');

        $output->writeln(sprintf('┌ <fg=magenta>%s</>', $catalyst->name()));
        $loop = function (GMResource $resource, $level) use ($output, &$loop, $showId, $showType) {
            $number = 1;
            $parentCount = count($resource->getChildResources());
            foreach ($resource->getChildResources() as $resource) {
                $lineCharacter = '├';
                if ($parentCount == $number) {
                    $lineCharacter = '└';
                }
                $output->writeln(
                    sprintf(
                        '%s─── <fg=%s>%s</> %s %s',
                        str_repeat('│    ', $level) . $lineCharacter,
                        $resource->isFolder() ? 'yellow' : 'green',
                        $resource->getName(),
                        $showId ? '[<fg=cyan>'.$resource->id.'</>]' : '',
                        $showType ? '[<fg=magenta>'.$resource->getTypeName().'</>]' : ''
                    )
                );
                if ($resource->isFolder()) {
                    $loop($resource, $level+1);
                }
                $number++;
            }
        };

        $loop($catalyst->YoYoProjectEntity()->getRoot()->gmResource(), 0);

        /*
        $loop = function ($children, $level) {
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
        };

        $loop($catalyst->YoYoProjectEntity()->_root, 0);
        */
        //$catalyst->YoYoProjectEntity()->getRoot()->gmResource()->isFolder()
        /*foreach ( as $resource) {
            $output->writeln('[<fg=cyan>'.$resource->gmResource()->id.'</>] [<fg=green>'.$resource->gmResource()->modelName.'</><fg=red>/'.$resource->gmResource()->filterType.'</>] - ' . $resource->gmResource()->getName() . "\t");
        }*/
        //$output->writeln('[<fg=cyan>'.$resource->gmResource()->id.'</>] [<fg=green>'.$resource->gmResource()->modelName.'</><fg=red>/'.$resource->gmResource()->filterType.'</>] - ' . $resource->gmResource()->getName() . "\t");

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