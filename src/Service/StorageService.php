<?php

namespace Catalyst\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;

class StorageService
{
    public function __construct()
    {
        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
    }

    public function fileExists(string $file):bool {
        return file_exists($file) && is_file($file);
    }

    public function writeFile(string $filename, string $contents)
    {
        $GLOBALS['storage']['writes'][$filename] = $contents;
    }

    public function saveEntity(SaveableEntityInterface $entity)
    {
        $this->writeFile($entity->getFilePath(), $entity->getFileContents());
    }

    public function persist() {
        //Should persist all changes on disk
        foreach ($GLOBALS['storage']['writes'] as $filename => $contents) {
            file_put_contents($filename, $contents);
        }
        $GLOBALS['storage']['writes'] = [];
    }

    public function getContents($path)
    {
        return file_get_contents($path);
    }

    public function getJson($path) : \stdClass
    {
        $output = json_decode($this->getContents($path));
        if (null === $output) {
            throw new MalformedJsonException($path . ' is not a valid JSON file.');
        }
        return $output;
    }
}