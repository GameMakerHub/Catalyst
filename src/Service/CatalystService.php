<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;
use Catalyst\Interfaces\SaveableEntityInterface;

class CatalystService
{
    /** @var CatalystEntity */
    private $thisCatalyst;

    /** @var array */
    private $keysToRemove = [];

    /** @var array */
    private $idsToRemove = [];

    public function persist() : void
    {
        StorageService::getInstance()->persist();
    }

    public function createNew(
        string $name,
        string $description,
        string $license,
        string $homepage,
        string $yyp
    ) : CatalystEntity {
        $entity = CatalystEntity::createNew(
            '.', $name, $description, $license, $homepage, $yyp
        );

        StorageService::getInstance()->saveEntity($entity);
        return $entity;
    }

    public function load(string $path) : CatalystEntity
    {
        $entity = CatalystEntity::createFromPath($path);
        return $entity;
    }

    public function existsHere() : bool
    {
        return $this->existsAt('.');
    }

    public function existsAt(string $path) : bool
    {
        return StorageService::getInstance()->fileExists($path . '/catalyst.json');
    }

    /**
     * @todo
     */
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