<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\YoYo\Resource\GM\GMResource;
use Symfony\Component\Console\Output\OutputInterface;

class CleanService
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

    public function clean(CatalystEntity &$project)
    {
        $this->project = $project;
        $this->writeLine('Removing all vendored files...');

        // Remove everything thats in the vendored folders
        $this->loop($this->project->YoYoProjectEntity()->getRoot()->gmResource());

        foreach ($this->project->ignored() as $leftOverFile) {
            if (!stripos($leftOverFile, 'datafile') === 0) {
                throw new \Exception(
                    'Ignored path ' . $leftOverFile . ' in gitignore is not a datafile and was not removed.'
                    . ' This looks like the project / Catalyst is broken! Please report as a bug, or fix manually.'
                );
            }

            // Get the UUID so we can remove that one from the project
            $resource = $this->project->YoYoProjectEntity()->getByInternalPath(
                'datafiles/' . substr(basename($leftOverFile), 0, strlen(basename($leftOverFile))-3)
            );
            if ($resource) {
                $this->project->YoYoProjectEntity()->removeUuidReference($resource->id);
            }

            StorageService::getInstance()->delete($leftOverFile);
            $this->project->removeIgnore($leftOverFile);
        }

        // Now loop through the current 1st level resources and save those (they might have contained the vendor folder)
        foreach ($this->project->YoYoProjectEntity()->getRoot()->gmResource()->getChildResources() as $resource) {
            if ($resource->isFolder()) {
                $resource->forceRegenerationOnSave();
                StorageService::getInstance()->saveEntity($resource);
            }
        }
    }

    private function loop(GMResource $resource, $level = 0, $delete = false)
    {
        foreach ($resource->getChildResources() as $resource) {
            if ($resource->isFolder()) {
                $delete = ($resource->getName() == 'vendor' || $delete); //$todo replace with constant
                // Loop through if this is a folder - delete if this name is vendor or delete flag is already on
                $this->loop($resource, $level+1, $delete);

                $thisPath = $resource->getFilePath();
            } else {
                $thisPath = StorageService::getInstance()->getAbsoluteFilename($resource->getFilePath() . '/../');
            }

            // Remove the resource from the entire project
            if ($delete) {
                // Remove from gitignore
                $this->project->removeIgnore($thisPath);

                // Remove the files
                StorageService::getInstance()->delete($thisPath);

                // Remove all references in the project
                $this->project->YoYoProjectEntity()->removeUuidReference($resource->id);
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