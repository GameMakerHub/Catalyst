<?php

namespace Catalyst\Tests\Service;

use Catalyst\Exception\PackageNotFoundException;

class PackageNotFoundExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testException()
    {
        $exception = new PackageNotFoundException('package/name', '1.0.0');
        $this->assertSame($exception->getMessage(), 'package/name with version 1.0.0 was not found');
    }
}