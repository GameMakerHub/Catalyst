#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
var_dump((new \GMM\Entities\ProjectEntity())->testValue());
$app = new Application();
$app->run();

