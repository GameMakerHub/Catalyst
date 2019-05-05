<?php

namespace Catalyst\Exception;

class PackageNotFoundException extends \InvalidArgumentException
{
    public function __construct(string $packageName, string $packageVersion)
    {
        parent::__construct($packageName . ' with version ' . $packageVersion . ' was not found');
    }
}