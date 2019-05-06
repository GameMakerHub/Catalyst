<?php

namespace Catalyst\Tests;

use Webmozart\Assert\Assert;

class TestHelper {
    public static function getTestCatalystEntity() : \Catalyst\Entity\CatalystEntity
    {
        return \Catalyst\Entity\CatalystEntity::createNew(
            '.',
            'project/name',
            'A description for the project',
            'MIT',
            'https://github.com/my/repository',
            'somefile.yyp'
        );
    }

    public static function getMockFile($mockFile) : string
    {
        $file = __DIR__ . '/../mocks/' . $mockFile;
        Assert::fileExists($file);
        return file_get_contents($file);
    }
}