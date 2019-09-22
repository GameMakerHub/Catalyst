<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
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

    public function install(CatalystEntity &$project)
    {
        $this->project = $project;
        $this->writeLine('Resolving dependencies and building installation list...');
        $packagesToInstall = $this->packageService->solveDependencies($project);
        $this->writeLine('Done!');
        foreach ($packagesToInstall as $package => $version) {
            $this->writeLine('Installing <fg=green>' . $package . '</>@<fg=cyan>' . $version . '</>...');
            $package = $this->packageService->getPackage($package, $version);

            $this->loop($package->YoYoProjectEntity()->getRoot()->gmResource(), $package);
        }
    }

    private function loop(GMResource $resource, CatalystEntity $packageToInstall, $level = 0, $targetDirectory = '')
    {
        foreach ($resource->getChildResources() as $resource) {

            if ($resource->isFolder()) {
                // Loop through if this is a folder
                if ($level == 0) {
                    $this->loop($resource, $packageToInstall, $level+1, $resource->getName() . '/vendor/' . $packageToInstall->name());
                } else {
                    $this->loop($resource, $packageToInstall, $level+1, $targetDirectory . '/' . $resource->getName());
                }
            } else {
                // This is an actual resource
                if ($resource->isOption()) {
                    //@todo add isConfig ?
                    continue;
                }

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

                // Write the actual files
                if ($resource->isIncludedFile()) {
                    $folder = $this->project->YoYoProjectEntity()->createFolderIfNotExists($this->project, $resource->filePath, $resource->getTypeName());

                    // Copy the .yy file
                    StorageService::getInstance()->copy($packageToInstall->path() . '/' . $resource->getFilePath(), $resource->getFilePath());
                    $this->project->addIgnore($resource->getFilePath());

                    // Copy the datafile
                    $dataFilePath = $resource->filePath . '/' .$resource->fileName;
                    StorageService::getInstance()->copy(
                        $packageToInstall->path() . '/' . $dataFilePath,
                        $dataFilePath
                    );
                    $this->project->addIgnore($dataFilePath);
                } else {
                    // Add it into the vendor folder
                    $folder = $this->project->YoYoProjectEntity()->createFolderIfNotExists($this->project, $targetDirectory, $resource->getTypeName());

                    // Stored in a folder with potentially multiple files
                    $localizedPath = $resource->getFilePath() . '/../';
                    StorageService::getInstance()->recursiveCopy($packageToInstall->path() . '/' . $localizedPath, $localizedPath);

                    // Add the file to the ignore list
                    $fullPath = StorageService::getInstance()->getAbsoluteFilename($localizedPath);
                    $this->project->addIgnore($fullPath);
                }

                // Link the resource to the folder
                $folder->addNewChildResource($resource);

                // Add the file to the project
                $this->project->YoYoProjectEntity()->addResource($resource);
            }
        }
    }

    private function writeLine($string)
    {
        if (null !== $this->output) {
            $this->output->writeln($string);
        }
    }
}