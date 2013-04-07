<?php
/**
 * Created by JetBrains PhpStorm.
 * User: flatline
 * Date: 4/6/13
 * Time: 7:22 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Flatline\Command;


use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    protected $hostsfile = '/etc/hosts';

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        // Check if hosts file exists
        if (!file_exists($this->hostsfile)) {
            $out->writeln("<error>Couldn't find hosts file at '{$this->hostsfile}'!</error>");
            exit;
        }
    }
}