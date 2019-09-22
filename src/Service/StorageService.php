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

    public static function assertFileExists(string $filename):bool {
        $filename = StorageService::getInstance()->makeRealFilename($filename);
        if (!self::getInstance()->fileExists($filename)) {
            throw new FileNotFoundException('File does not exist or is not a file: ' . $filename);
        }
        return true;
    }

    public function fileExists(string $filename):bool {
        $filename = $this->makeRealFilename($filename);
        if (isset($GLOBALS['storage']['writes'][$filename])) {
            return true;
        }
        return file_exists($filename) && is_file($filename);
    }

    public function writeYYFile(string $filename, \stdClass $content)
    {
        $this->writeFile($filename, JsonService::encode($content));
    }

    public function writeFile(string $filename, string $contents)
    {
        $filename = $this->makeRealFilename($filename);
        $GLOBALS['storage']['writes'][$filename] = $contents;
    }

    public function saveEntity(SaveableEntityInterface $entity)
    {
        self::getInstance()->writeFile($entity->getFilePath(), $entity->getFileContents());
    }

    public function persist($dryRun = false) {
        //Should persist all changes on disk
        foreach ($GLOBALS['storage']['writes'] as $filename => $contents) {
            if ($dryRun) {
                echo 'Dry-run: not writing to ' . $filename . PHP_EOL;
            } else {
                @mkdir(dirname($filename), 0777, true);
                file_put_contents($filename, $contents);
            }
        }
        $GLOBALS['storage']['writes'] = [];
    }

    public function getFromWriteStorage($filename)
    {
        $filename = $this->makeRealFilename($filename);
        if (isset($GLOBALS['storage']['writes'][$filename])) {
            return $GLOBALS['storage']['writes'][$filename];
        }
        throw new \Exception('File not written in storage: ' . $filename);
    }

    private function makeRealFilename($filename)
    {
        if (!$this->pathIsAbsolute($filename)) {
            //echo 'Relative path detected: ' . $filename . ' - prepending ' . getcwd() . PHP_EOL;
            $filename = getcwd() . '/' . $filename;
        }
        return str_replace('\\', '/', $filename);
    }

    private function pathIsAbsolute($path) {
        if($path === null || $path === '') return false;
        return $path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i',$path) > 0;
    }

    public function getContents($path): string
    {
        $path = str_replace('\\', '/', $path);
        try {
            return $this->getFromWriteStorage($path);
        } catch (\Exception $e) {
            self::assertFileExists($path);
            return file_get_contents($path);
        }
    }

    public function getJson($path): \stdClass
    {
        try {
            return JsonService::decode(self::getInstance()->getContents($path));
        } catch (MalformedJsonException $e) {
            throw new MalformedJsonException($path . ' is not a valid JSON file: ' . $e->getMessage());
        }
    }

    public function rrmdir($path) {
        // @todo make this work with persist as well
        $i = new \DirectoryIterator($path);
        foreach($i as $f) {
            if($f->isFile()) {
                unlink($f->getRealPath());
            } else if(!$f->isDot() && $f->isDir()) {
                $this->rrmdir($f->getRealPath());
            }
        }
        rmdir($path);
    }

    public function recursiveCopy($from, $to)
    {
        $from = $this->getAbsoluteFilename($from);
        $to = $this->getAbsoluteFilename($to);
        foreach (glob($from . '/*') as $filename) {
            $target = $this->makeRealFilename($to . '/' . basename($filename));
            $GLOBALS['storage']['writes'][$target] = file_get_contents($filename);
            //echo 'Copy from ' . $filename . ' to ' . $target . PHP_EOL;
        }
    }

    public function getAbsoluteFilename($filename) {
        $path = [];
        $filename = str_replace('\\', '/', $filename);
        foreach(explode('/', $filename) as $part) {
            // ignore parts that have no value
            if (empty($part) || $part === '.') continue;

            if ($part !== '..') {
                // cool, we found a new part
                array_push($path, $part);
            }
            else if (count($path) > 0) {
                // going back up? sure
                array_pop($path);
            } else {
                // now, here we don't like
                throw new \Exception('Climbing above the root is not permitted.');
            }
        }

        // prepend my root directory
        //array_unshift($path, '');

        return join('/', $path);
    }
}