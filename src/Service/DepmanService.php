<?php

namespace Catalyst\Service;

use Catalyst\Entity\DepManEntity;

class DepmanService
{
    /** @var DepManEntity */
    private $thisDepMan;

    /** @var array */
    private $keysToRemove = [];

    /** @var array */
    private $idsToRemove = [];

    public function __construct()
    {
        $this->thisDepMan = new DepManEntity(realpath('.'));
    }

    public function uninstallAll() {
        $project = $this->thisDepMan->projectEntity();

        //$output->writeln('<fg=green>-</> ROOT');
        $this->loopIn($project->getChildren(), 0);

        foreach ($this->keysToRemove as $key) {
            $this->thisDepMan->projectEntity()->removeResource($key);
        }

        foreach ($this->idsToRemove as $id) {
            foreach ($this->thisDepMan->projectEntity()->getChildren() as $child) {
                $child->removeChild($id);
            }
            foreach ($this->thisDepMan->projectEntity()->script_order as $key => $val) {
                if ($val == $id) {
                    unset($this->thisDepMan->projectEntity()->script_order[$key]);
                }
            }
        }

        $this->thisDepMan->projectEntity()->save();
        $this->thisDepMan->save();
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