<?php
use Symfony\Component\DependencyInjection\Definition;

$definition = new Definition();

$definition->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(false);

$this->registerClasses($definition, 'Catalyst\\', '../src/*');

$definitionPublic = new Definition();

$definitionPublic->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true);

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$container->getDefinition('Symfony\\Component\\Console\\Application')->setPublic(true);