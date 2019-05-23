<?php

namespace Catalyst\Service;

use Catalyst\Exception\FileNotFoundException;
use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;

class StorageService
{
    /** @var StorageService */
    private static $instance = null;

    private function __construct()
    {
        //@todo remove GLOBALS usage because we're in a singleton anyway
        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
    }

    /**
     * @deprecated ONLY FOR TEST USAGE
     */
    public static function setInstance($instance)
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

    public static function assertFileExists(string $file):bool {
        if (!self::getInstance()->fileExists($file)) {
            throw new FileNotFoundException('File does not exist or is not a file: ' . $file);
        }
        return true;
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
        self::assertFileExists($path);
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