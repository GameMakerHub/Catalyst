<?php

namespace Catalyst\Tests\Service;

use Catalyst\Exception\MalformedJsonException;
use Catalyst\Model\Repository;
use Catalyst\Service\PackageService;
use Catalyst\Service\StorageService;
use Catalyst\Tests\TestHelper;

class PackageServiceTest extends \PHPUnit\Framework\TestCase
{
    /** @var PackageService */
    private $subject;

    protected function setUp() : void
    {
        $this->subject = new PackageService();
    }

    /**
     * @dataProvider provideSimpleResolveTestPackages
     */
    public function testDependencyResolvingSimple($requirements, $expected)
    {
        $repositoryData = $this->getSimpleRepositoryData();

        $repository = \Mockery::mock(Repository::class);

        $repository->shouldReceive('')

        $this->assertSame($expected, $this->subject->solveDependencies($repository, $requirements));
    }

    public static function provideSimpleResolveTestPackages()
    {
        return [
            'Simple single test package' => [
                '$requirements' => ['dukesoft/test-package' => '^1.0'],
                '$expected' => ['']
            ]
        ];
    }

    private function getSimpleRepositoryData()
    {
        $repository = $this->getEmptyRepositoryData();
        $repository->packages[] = [
            'name' => 'dukesoft/test-package',
            'source' => 'git@github.com:dukesoft/test-package.git',
            'versions' => [
                [
                    'version' => 'v1.0.0',
                    'dependencies' => [],
                ],
                [
                    'version' => '1.0.1',
                    'dependencies' => [],
                ],
                [
                    'version' => 'v1.0.2',
                    'dependencies' => [],
                ],
                [
                    'version' => '1.430.429',
                    'dependencies' => [],
                ],
                [
                    'version' => 'testingbranch',
                    'dependencies' => [],
                ],
                [
                    'version' => 'develop',
                    'dependencies' => [],
                ],
            ]
        ],
        [
            'name' => 'othervendor/test-package',
            'source' => 'git@github.com:othervendor/test-package.git',
            'versions' => [
                [
                    'version' => '1.0.1',
                    'dependencies' => [],
                ],
                [
                    'version' => 'v1.0.2',
                    'dependencies' => [],
                ],
            ]
        ],
         [
             'name' => 'dukesoft/anotherpackage',
             'source' => 'git@github.com:dukesoft/anotherpackage.git',
             'versions' => [
                 [
                     'version' => '0.0.1',
                     'dependencies' => [],
                 ],
                 [
                     'version' => '0.1.0',
                     'dependencies' => [],
                 ],
                 [
                     'version' => '1.0.0',
                     'dependencies' => [],
                 ],
                 [
                     'version' => '2.0.0',
                     'dependencies' => [],
                 ],
             ]
         ];
    }

    private function getEmptyRepositoryData()
    {
        $repository = new \stdClass();
        $repository->type = 'GMDepMan Repository';
        $repository->packages = [];
    }

}