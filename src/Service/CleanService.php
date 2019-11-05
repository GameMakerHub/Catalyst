<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Model\YoYo\Resource\GM\GMFolder;
use Catalyst\Model\YoYo\Resource\GM\GMIncludedFile;
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
            if (strlen($leftOverFile) <= 1) {
                continue;
            }
            $resource = false;

            // OK to delete datafiles
            if (stripos($leftOverFile, 'datafiles/') === 0) {
                // Just a datafile, delete it
                StorageService::getInstance()->delete($leftOverFile);
                $this->project->removeIgnore($leftOverFile);
                continue;
            }

            // YY data file - contains UUID we need to remove
            if (stripos($leftOverFile, 'datafiles_yy/') === 0) {
                $resource = GMIncludedFile::createFromFile($leftOverFile);
            }

            // OK to delete folders (only the ones that contained data files)
            if (stripos($leftOverFile, 'views/') === 0) {
                $resource = GMFolder::createFromFile($leftOverFile);
                if ($resource->filterType !== 'IncludedFile') {
                    $resource = false;
                }
            }

            if ($resource === false) {
                throw new \Exception(
                    'Ignored path ' . $leftOverFile . ' in gitignore is not a datafile and was not removed.'
                    . ' This looks like the project / Catalyst is broken! Please report as a bug, or fix manually.'
                );
            }

            $this->project->YoYoProjectEntity()->removeUuidReference($resource->id);
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
                // Loop through if this is a folder - delete if this name is vendor or delete flag is already on
                $this->loop($resource, $level+1, ($resource->getName() == 'vendor' || $delete));
                $thisPath = $resource->getFilePath();
            } else {
                $thisPath = StorageService::getInstance()->getAbsoluteFilename($resource->getFilePath() . '/../');
            }

            // Remove the resource from the entire project, or the ROOT vendor folder
            if ($delete || ($resource->isFolder() && $resource->getName() == 'vendor' && $level == 1)) {
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