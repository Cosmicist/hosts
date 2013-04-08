#!/usr/bin/env php
<?php

require "vendor/autoload.php";

use Flatline\Command\Hosts\AddCommand;
use Flatline\Command\Hosts\ListCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AddCommand());
$application->add(new ListCommand());
$application->run();