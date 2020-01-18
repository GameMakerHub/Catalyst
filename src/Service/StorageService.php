<?php

namespace Catalyst\Service;

use Catalyst\Exception\FileNotFoundException;
use Catalyst\Exception\MalformedJsonException;
use Catalyst\Interfaces\SaveableEntityInterface;

class StorageService
{
    /** @var StorageService */
    private static $instance = null;

    public function __construct()
    {
        //@todo remove GLOBALS usage because we're in a singleton anyway
        if (!array_key_exists('storage', $GLOBALS)) {
            $GLOBALS['storage'] = [];
        }
        if (!array_key_exists('writes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['writes'] = [];
        }
        if (!array_key_exists('deletes', $GLOBALS['storage'])) {
            $GLOBALS['storage']['deletes'] = [];
        }
    }

    /**
     * @deprecated ONLY FOR TEST USAGE
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    /**
     * @deprecated ONLY FOR TEST USAGE
     */
    public static function reset()
    {
        unset($GLOBALS['storage']);
        self::$instance = null;
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
        if (isset($GLOBALS['storage']['deletes'][$filename])) {
            return false;
        }

        if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
            // Make windows style directories if we're on windows
            $filename = str_replace('/', '\\', $filename);
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
        if (isset($GLOBALS['storage']['deletes'][$filename])) {
            unset($GLOBALS['storage']['deletes'][$filename]);
        }
    }

    public function delete(string $pathOrFile)
    {
        $pathOrFile = $this->makeRealFilename($pathOrFile);
        $files = [$pathOrFile];
        if (is_dir($pathOrFile)) {
            $files = [];
            //We have multiple files in this directory!
            foreach (glob($pathOrFile . '/**') as $globbed) {
                $files[] = $globbed;
            }
        }

        foreach ($files as $filename) {
            $this->deleteFile($filename);
        }

        $this->deleteFile($pathOrFile);
    }

    public function deleteFile(string $filename)
    {
        if (isset($GLOBALS['storage']['writes'][$filename])) {
            unset($GLOBALS['storage']['writes'][$filename]);
        }
        $GLOBALS['storage']['deletes'][$filename] = true;
    }

    public function saveEntity(SaveableEntityInterface $entity)
    {
        self::getInstance()->writeFile($entity->getFilePath(), $entity->getFileContents());
    }

    /**
     * Persist all data on disk
     * @param bool $dryRun
     */
    public function persist($dryRun = false) {

        foreach ($GLOBALS['storage']['deletes'] as $filename => $contents) {
            if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
                // Make windows style directories if we're on windows
                $filename = str_replace('/', '\\', $filename);
            }

            if ($dryRun) {
                echo 'Dry-run: not deleting ' . $filename . PHP_EOL;
            } else {
                if (is_dir($filename)) {
                    $this->rrmdir($filename);
                } else {
                    @unlink($filename); //@todo remove the error supression, just doing this for the initial "install"..
                }
            }
        }
        $GLOBALS['storage']['deletes'] = [];

        foreach ($GLOBALS['storage']['writes'] as $filename => $contents) {
            if ($dryRun) {
                echo 'Dry-run: not writing to ' . $filename . PHP_EOL;
            } else {
                if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
                    // Make windows style directories if we're on windows
                    $filename = str_replace('/', '\\', $filename);
                }
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

    public static function pathToAbsolute($pathOrFile)
    {
        $inst = self::getInstance();
        return $inst->getAbsoluteFilename($inst->makeRealFilename($pathOrFile));
    }

    private function makeRealFilename($filename)
    {
        if (!$this->pathIsAbsolute($filename)) {
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
            if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
                // Make windows style directories if we're on windows
                $path = str_replace('/', '\\', $path);
            }
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

    /**
     * @deprecated WARNING: This actually RECURSIVELY REMOVES A DIRECTORY WITHOUT USING PERSIST.
     * @param $path
     */
    private function rrmdir($path) {
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

    public function copy($from, $to)
    {
        $from = $this->getAbsoluteFilename($from);
        $to = $this->makeRealFilename($to);
        $GLOBALS['storage']['writes'][$to] = file_get_contents($from);
        if (isset($GLOBALS['storage']['deletes'][$to])) {
            unset($GLOBALS['storage']['deletes'][$to]);
        }
    }

    public function recursiveCopy($from, $to)
    {
        $from = $this->getAbsoluteFilename($from);
        $to = $this->getAbsoluteFilename($to);
        foreach (glob($from . '/*') as $filename) {

            if (is_dir($filename)) {
                $this->recursiveCopy($filename, $to . '/' . basename($filename));
            } else {
                $target = $this->makeRealFilename($to . '/' . basename($filename));
                $GLOBALS['storage']['writes'][$target] = file_get_contents($filename);
                if (isset($GLOBALS['storage']['deletes'][$target])) {
                    unset($GLOBALS['storage']['deletes'][$target]);
                }
            }
        }
    }

    public function resolvePath($filename)
    {
        $winRoot = false;
        $linuxRoot = false;
        $path = [];
        $filename = str_replace('\\', '/', $filename);

        if (substr($filename, 0, 1) === '/') {
            $linuxRoot = true;
        }
        if (substr($filename, 1, 1) === ':') {
            $winRoot = true;
        }


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
        if ($linuxRoot) {
            return '/' . join('/', $path);
        }
        if ($winRoot) {
            return join('/', $path);
        }
        return join('/', $path);
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

        if ($this->pathIsAbsolute($filename)) {
            if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') === 0) {
                return join('/', $path); // Root is included in file on windows
            }
            return '/' . join('/', $path); // Root when running on linux
        } else {
            return str_replace('\\', '/', getcwd() . '/') . join('/', $path);
        }
    }
}