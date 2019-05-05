<?php

namespace Catalyst\Service;

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
        return file_exists($file);
    }

    public function writeFile(string $filename, string $contents)
    {
        $GLOBALS['storage']['writes'][$filename] = $contents;
    }

    public function persist() {
        //Should persist all changes on disk
        foreach ($GLOBALS['storage']['writes'] as $filename => $contents) {
            file_put_contents($filename, $contents);
        }
        $GLOBALS['storage']['writes'] = [];
    }
}