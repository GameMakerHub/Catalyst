#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$kernel = new \Catalyst\Kernel;
$kernel->boot();

$container = $kernel->getContainer();
$application = $container->get(Application::class);
$application->run();