<?php

namespace Hosts\Console;

use Hosts\Command\AddCommand;
use Hosts\Command\ShowCommand;
use Hosts\Command\ToggleCommand;
use Symfony\Component\Console\Application as BaseApp;

class Application extends BaseApp
{
    public function __construct()
    {
        parent::__construct('Hosts manager', '@package_version@');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new ShowCommand();
        $commands[] = new AddCommand();
        $commands[] = new ToggleCommand();

        return $commands;
    }
}