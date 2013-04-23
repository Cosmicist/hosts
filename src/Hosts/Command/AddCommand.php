<?php

namespace Hosts\Command;

use Hosts\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("add")
            ->setDescription("Add a host")
            ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname you want to add')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Specify a different IP (other than 127.0.0.1)', '127.0.0.1')
            ->addOption('disabled', 'd', InputOption::VALUE_NONE, 'Add the host in [disabled] state')
        ;
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        // Check if hosts file is writable
        if (!is_writable($this->hostsfile)) {
            $this->error($out, "Can't write hosts file! Run the command as root.");
        }

        $ip = $in->getOption('ip');
        $disabled = $in->getOption('disabled') ? '#' : '';
        $hostname = $in->getArgument('hostname');

        // Check if host already exists
        if ($this->hostExists($hostname, $ip)) {
            $this->error($out, "Host '$hostname' already exists for IP $ip");
        }

        $disabledout = $disabled ? " <comment>(in disabled state)</comment>" : '';
        $out->writeln("Adding host <info>$hostname</info>$disabledout with ip <info>$ip</info>...");

        // Add host to hosts file
        file_put_contents($this->hostsfile, "{$disabled}$ip\t$hostname\n", FILE_APPEND);

        $this->success($out, "Host [$hostname] successfully added!");
    }
}