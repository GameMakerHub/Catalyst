#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
$app = new Application();
$app->add(new \GMM\Command\TestProjectCommand());
$app->run();

