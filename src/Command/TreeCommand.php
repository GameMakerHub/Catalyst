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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $catalyst = $this->catalystService->load(realpath('.'));
        } catch (\Exception $e) {
            $output->writeLn('<fg=red>ERROR LOADING CATALYST PROJECT FILE: </>' . $e->getMessage());
            return 126;
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
        return 0;
    }
}