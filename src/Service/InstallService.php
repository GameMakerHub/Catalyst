<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\Uuid;
use Catalyst\Model\YoYo\Resource\GM\GMResource;
use Symfony\Component\Console\Output\OutputInterface;

class InstallService
{
    /** @var OutputInterface */
    private $output;

    /** @var PackageService */
    private $packageService;

    /** @var CatalystEntity */
    private $project;

    public function __construct(PackageService $packageService)
    {
        $this->packageService = $packageService;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function install(CatalystEntity $project)
    {
        $this->project = $project;
        $packagesToInstall = $this->packageService->solveDependencies($project);

        foreach ($packagesToInstall as $package => $version) {
            $this->writeLine('Installing <fg=green>' . $package . '</>@<fg=cyan>' . $version . '</>...');
            $package = $this->packageService->getPackage($package, $version);

            $this->loop($package->YoYoProjectEntity()->getRoot()->gmResource(), $package);
        }
    }

    private function loop(GMResource $resource, CatalystEntity $packageToInstall, $level = 0, $targetDirectory = '')
    {
        //$number = 1;
        //$parentCount = count($resource->getChildResources());
        foreach ($resource->getChildResources() as $resource) {
            //echo '    Checking child resource ' . $resource->getName() . PHP_EOL;

            //$lineCharacter = '├';
            //if ($parentCount == $number) {
            //    $lineCharacter = '└';
            //}

            /*$this->writeLine(
                sprintf(
                    '%s─── <fg=%s>%s</> %s %s %s',
                    str_repeat('│    ', $level) . $lineCharacter,
                    $resource->isFolder() ? 'yellow' : 'green',
                    $resource->getName(),
                    $targetDirectory,
                    '[<fg=cyan>'.$resource->id.'</>]',
                    '[<fg=magenta>'.$resource->getTypeName().'</>]'
                )
            );*/


            if ($resource->isFolder()) {
                if ($level == 0) {
                    $this->loop($resource, $packageToInstall, $level+1, $resource->getName() . '/vendor/' . $packageToInstall->name());
                } else {
                    $this->loop($resource, $packageToInstall, $level+1, $targetDirectory . '/' . $resource->getName());
                }
            } else {
                if ($resource->isOption() || $resource->isIncludedFile()) {
                    //@todo add isConfig ?
                    //@todo add handler for included files
                    continue;
                }
                $folder = $this->project->YoYoProjectEntity()->createFolderIfNotExists($targetDirectory, $resource->getTypeName());
                echo 'Trying to add ' . $resource->getTypeName() . ' ('.$resource->name.') to ' . $targetDirectory . PHP_EOL;

                if ($this->project->YoYoProjectEntity()->resourceNameExists($resource->name)) {
                    throw new \Exception(
                        'Uh-oh! An asset name clash occured. We tried to add a resource (' . $resource->name
                        . ') from "' . $packageToInstall->name() . '" but a resource with that name '
                        . 'already exists in this project... Cant continue.'
                    );
                }

                if ($this->project->YoYoProjectEntity()->uuidExists($resource->id)) {
                    throw new \Exception(
                        'Uh-oh! A UUID clash occured. We tried to add a resource (' . $resource->name
                        . ') from "' . $packageToInstall->name() . '" but a resource with UUID ' . $resource->id
                        . ' already exists in this project... Cant continue.'
                    );
                }

                // Link the resource to the folder
                $folder->addNewChildResource($resource);

                // Write the actual file
                StorageService::getInstance()->recursiveCopy($packageToInstall->path() . '/' . $resource->getFilePath() . '/../', $resource->getFilePath() . '/../');

                // Add the file to the project
                $this->project->YoYoProjectEntity()->addResource($resource);

                // Add the file to the ignore list
                $fullPath = StorageService::getInstance()->getAbsoluteFilename($resource->getFilePath() . '/../');
                $this->project->addIgnore($fullPath);
            }
            //$number++;
        }
        //echo ' End of loop!' . PHP_EOL;
    }

    private function writeLine($string)
    {
        if (null !== $this->output) {
            $this->output->writeln($string);
        }
    }
}