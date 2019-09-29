#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use \Symfony\Component\Console\Application;

$kernel = new \Catalyst\Kernel;
$kernel->boot();

$container = $kernel->getContainer();

//Override symfony's settings for the help generation...
$_SERVER['PHP_SELF'] = 'catalyst';
$GLOBALS['SYMFONY_KERNEL'] = $kernel;

/** @var Application $application */
$application = $container->get(Application::class);
$application->setName('GameMakerHub Catalyst');
$application->setVersion('0.2.0');
$application->run();