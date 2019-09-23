<?php

namespace Catalyst\Service;

use Catalyst\Entity\CatalystEntity;

class CatalystService
{
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

    public function load(string $path = null) : CatalystEntity
    {
        if (null === $path) {
            $path = realpath('.');
        }
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
}