#!/usr/bin/env php
<?php
// application.php
require __DIR__.'/vendor/autoload.php';
use Symfony\Component\Console\Application;
use App\DbInitCommand;
use App\DbListCommand;
use App\DbInsertCommand;

$application = new Application();
$application->add(new DbInitCommand());
$application->add(new DbListCommand());
$application->add(new DbInsertCommand());
$application->run();
