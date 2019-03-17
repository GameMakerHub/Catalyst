<?php

namespace GMDepMan\Command;

use Composer\Semver\Semver;
use GMDepMan\Entity\DepManEntity;
use GMDepMan\Exception\UnresolveableDependenciesException;
use GMDepMan\Service\PackageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class CleanCommand extends Command
{
    protected static $defaultName = 'clean';

    /** @var PackageService */
    private $packageService;

    /** @var DepManEntity */
    private $thisDepMan;

    private $idsToRemove = [];

    private $keysToRemove = [];

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;

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
        $this->thisDepMan = new DepManEntity(realpath('.'));

        $project = $this->thisDepMan->projectEntity();

        $output->writeln('<fg=green>-</> ROOT');
        $this->loopIn($input, $output, $project->getChildren(), 0);

        foreach ($this->keysToRemove as $key) {
            $this->thisDepMan->projectEntity()->removeResource($key);
        }

        foreach ($this->idsToRemove as $id) {
            foreach ($this->thisDepMan->projectEntity()->getChildren() as $child) {
                $child->removeChild($id);
            }
        }

        $this->thisDepMan->projectEntity()->save();
        $this->thisDepMan->save();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param \GMDepMan\Model\YoYo\Resource\GM\GMResource[] $children
     * @param int $level
     * @param bool $remove
     */
    private function loopIn(InputInterface $input, OutputInterface $output, array $children, $level = 0, $remove = false) {
        foreach ($children as $child) {
            $name = '?';
            if (isset($child->folderName)) {
                $name = $child->folderName;
            } else if (isset($child->name)) {
                $name = $child->name;
            }
            $hasChildren = count($child->getChildren()) >= 1;
            if ($level > 0) {
                if ($name == 'vendor') {
                    $remove = true;
                }
                $output->writeln('<fg='.($remove ? 'red' : 'green') .'>' . str_repeat('|  ', $level).'\__</> ' . $name . '['.$child->id.']');
            }

            if ($hasChildren) {
                $this->loopIn($input, $output, $child->getChildren(), $level+1, $remove);
            }

            if ($remove) {
                $this->idsToRemove[] = $child->id;
                $this->keysToRemove[] = $child->getYypResource()->key();
                $child->delete();
            }

        }
    }

}