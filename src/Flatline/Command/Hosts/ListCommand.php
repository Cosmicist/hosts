<?php
/**
 * Created by JetBrains PhpStorm.
 * User: flatline
 * Date: 4/6/13
 * Time: 9:01 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Flatline\Command\Hosts;


use Flatline\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("list")
            ->setDescription("Show a list of all hosts")
            ->addOption('enabled-only', 'e', InputOption::VALUE_NONE, 'Show only enabled hosts')
            ->addOption('disabled-only', 'd', InputOption::VALUE_NONE, 'Show only disabled hosts')
            ->addOption('match', 'm', InputOption::VALUE_REQUIRED, 'Show hosts that match the specified regex pattern')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Show hosts for the specified IP only')
        ;
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        // Check if hosts file is writable
        if (!is_readable($this->hostsfile)) {
            $out->writeln("<error>Can't read hosts file! Run the command as root.</error>");
            exit;
        }

        // Read the hosts file
        $hosts = file_get_contents($this->hostsfile);
        $hosts = explode("\n", $hosts);

        // IP filter
        $only_for_ip = $in->getOption('ip');
        // Regex filter
        $match = $in->getOption('match');
        // Enabled-only filters
        $enabled_only = $in->getOption('enabled-only');
        // Disabled-only filters
        $disabled_only = $in->getOption('disabled-only');

        // Create a styles
        $out->getFormatter()->setStyle('matched', new OutputFormatterStyle('cyan', null, array('underscore')));
        $out->getFormatter()->setStyle('disabled', new OutputFormatterStyle('black', null, array('reverse')));

        // Group hosts by IP
        $hosts_by_ip = array();
        foreach ($hosts as $ln) {
            // Skip generic comments
            if (!preg_match('/^#?\d+.\d+.\d+.\d+/', $ln)) {
                continue;
            }

            // Split ip from host(s)
            list($ip, $host) = preg_split('/[\s\t]+/', $ln, 2);

            // Filter hosts by IP
            if ($only_for_ip and $only_for_ip !== $ip) {
                continue;
            }

            // Filter hosts by pattern
            if ($match and !preg_match("/$match/i", $host)) {
                continue;
            }

            // Highlight matches
            if ($match) {
                $host = preg_replace("/($match)/i", '<matched>$1</matched>', $host);
            }

            // Highlight disabled
            if (strncmp($ln, '#', 1) == 0) {
                $host = "<disabled>$host</disabled>";
            }

            // @todo enabled/disabled only filter

            $hosts_by_ip[trim($ip, '#')][] = $host;
        }

        foreach ($hosts_by_ip as $ip => $host_list) {
            $out->writeln("Hosts for IP <info>$ip</info>");

            foreach ($host_list as $host) {
                $out->writeln(" - <comment>$host</comment>");
            }

            $out->writeln("");
        }
    }
}