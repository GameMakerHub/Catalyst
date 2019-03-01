<?php

namespace GMDepMan;

use GMDepMan\DependencyInjection\CompilerPass\CollectCommandsToApplicationCompilerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        //$loader->load(__DIR__ . '/../config/services.php'); //@todo, so we can remove YAML
    }

    protected function build(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addCompilerPass(new CollectCommandsToApplicationCompilerPass);
    }
}
