<?php

namespace Flatline\Command\Hosts;

use Flatline\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("add")
            ->setDescription("Add a host")
            ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname you want to add')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Specify a different IP (other than 127.0.0.1)', '127.0.0.1');
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        // Check if hosts file is writable
        if (!is_writable($this->hostsfile)) {
            $out->writeln("<error>Can't write hosts file! Run the command as root.</error>");
            exit;
        }

        $hostname = $in->getArgument('hostname');
        $ip = $in->getOption('ip');

        $out->writeln("Adding host <info>$hostname</info> with ip <info>$ip</info>...");

        // Add host to hosts file

        $out->writeln("<info>Done!</info>");
    }
}