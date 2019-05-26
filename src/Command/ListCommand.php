<?php

namespace Catalyst\Command;

use Catalyst\Service\CatalystService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected static $defaultName = 'list';

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
            ->setDescription('List the projects assets')
            ->setHelp('List all project assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $catalyst = $this->catalystService->load(realpath('.'));
        } catch (\Exception $e) {
            $output->writeLn('<fg=red>ERROR LOADING CATALYST PROJECT FILE: </>' . $e->getMessage());
            return 127;
        }

        foreach ($catalyst->YoYoProjectEntity()->resources as $resource) {

            $output->writeln(
                sprintf(
                    '[<fg=cyan>%s</>] [<fg=green>%s</><fg=red>%s</>] - %s',
                    $resource->gmResource()->id,
                    $resource->gmResource()->modelName,
                    $resource->gmResource()->filterType ? ' / ' . $resource->gmResource()->filterType : '',
                    $resource->gmResource()->getName()
                )
            );
        }
    }
}