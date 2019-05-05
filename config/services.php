<?php
////@TODO
// config/services.php
use Symfony\Component\DependencyInjection\Definition;

// To use as default template
$definition = new Definition();

$definition
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(false)
;

// $this is a reference to the current loader
$this->registerClasses($definition, 'Catalyst\\', '../src/*');

// Public
// To use as default template
$definitionPublic = new Definition();

$definitionPublic
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true)
;

/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */
$container->getDefinition('Symfony\\Component\\Console\\Application')->setPublic(true);


//$this->registerClass($definitionPublic, 'Symfony\\Component\\Console\\Application', '../vendor/symfony/console/Application.php');