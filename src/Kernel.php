<?php

namespace Catalyst;

use Catalyst\DependencyInjection\CompilerPass\CollectCommandsToApplicationCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{

    public function __construct()
    {
        parent::__construct('dev', getenv('DEBUG'));
    }

    public function registerBundles(): array
    {
        return [];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/../config/services.yml');
        //$loader->load(__DIR__ . '/../config/services.php'); //@todo, so we can remove YAML + yaml parser
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/gmh_catalyst/cache';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir().'/gmh_catalyst/log';
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CollectCommandsToApplicationCompilerPass);
    }
}
