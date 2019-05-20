<?php

namespace Catalyst\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;

class StorageService
{
    private static $instance = null;

    private function __construct()
    {
        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
    }

    // for testing and mocking purposes
    public static function setInstance(StorageService $instance)
    {
        self::$instance = $instance;
    }

    public static function getInstance(): StorageService
    {
        if (self::$instance == null) {
            self::$instance = new StorageService();
        }

        return self::$instance;
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
        self::getInstance()->writeFile($entity->getFilePath(), $entity->getFileContents());
    }

    public function persist() {
        //Should persist all changes on disk
        foreach ($GLOBALS['storage']['writes'] as $filename => $contents) {
            file_put_contents($filename, $contents);
        }
        $GLOBALS['storage']['writes'] = [];
    }

    public function getContents($path): string
    {
        return file_get_contents($path);
    }

    public function getJson($path): \stdClass
    {
        try {
            return JsonService::decode(self::getInstance()->getContents($path));
        } catch (MalformedJsonException $e) {
            throw new MalformedJsonException($path . ' is not a valid JSON file: ' . $e->getMessage());
        }
    }
}