<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\SaveableEntityInterface;

class CatalystService
{
    /** @var CatalystEntity */
    private $thisCatalyst;

    /** @var array */
    private $keysToRemove = [];

    /** @var array */
    private $idsToRemove = [];

    /** @var StorageService */
    private $storageService;

    public function __construct(StorageService $storageService)
    {
        $this->storageService = $storageService;
    }

    public function persist() : void
    {
        $this->storageService->persist();
    }

    public function createNew($name, $description, $license, $homepage, $yyp) : CatalystEntity
    {
        $entity = CatalystEntity::createNew(
            '.', $name, $description, $license, $homepage, $yyp
        );

        $this->storageService->saveEntity($entity);
    }

    public function existsHere()
    {
        return $this->existsAt('.');
    }

    public function existsAt(string $path)
    {
        return $this->storageService->fileExists($path . '/catalyst.json');
    }

    public function uninstallAll() {
        $project = $this->thisCatalyst->projectEntity();

        //$output->writeln('<fg=green>-</> ROOT');
        $this->loopIn($project->getChildren(), 0);

        foreach ($this->keysToRemove as $key) {
            $this->thisCatalyst->projectEntity()->removeResource($key);
        }

        foreach ($this->idsToRemove as $id) {
            foreach ($this->thisCatalyst->projectEntity()->getChildren() as $child) {
                $child->removeChild($id);
            }
            foreach ($this->thisCatalyst->projectEntity()->script_order as $key => $val) {
                if ($val == $id) {
                    unset($this->thisCatalyst->projectEntity()->script_order[$key]);
                }
            }
        }

        $this->thisCatalyst->projectEntity()->save();
        $this->thisCatalyst->save();
    }

    /**
     * @param \Catalyst\Model\YoYo\Resource\GM\GMResource[] $children
     * @param int $level
     * @param bool $remove
     */
    private function loopIn(array $children, $level = 0, $remove = false) {
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
                echo ('<fg='.($remove ? 'red' : 'green') .'>' . str_repeat('|  ', $level).'\__</> ' . $name . '['.$child->id.']' . PHP_EOL);
            }

            if ($hasChildren) {
                $this->loopIn($child->getChildren(), $level+1, $remove);
            }

            if ($remove) {
                $this->idsToRemove[] = $child->id;
                $this->keysToRemove[] = $child->getYypResource()->key();
                $child->delete();
            }
        }
    }
}