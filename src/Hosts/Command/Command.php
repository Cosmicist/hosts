<?php

namespace Hosts\Command;


use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Command extends BaseCommand
{
    protected $hostsfile = '/etc/hosts';

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        // Check if hosts file exists
        if (!file_exists($this->hostsfile)) {
            $this->showBlock($out, "Couldn't find hosts file at '{$this->hostsfile}'!");
            exit;
        }
    }

    protected function hostExists($host, $ip = '127.0.0.1')
    {
        $hosts = $this->parseHosts(array(), false);

        if ($ip === 'any') {
            // Merge all hosts in a one-dimension array
            $grouped = array_values($hosts);
            $hosts = array();
            foreach ($grouped as $group) {
                $hosts = array_merge($hosts, $group);
            }
        } else {
            $hosts = (array)@$hosts[$ip];
        }

        return in_array($host, $hosts);
    }

    protected function parseHosts(array $filters = array(), $styled = true)
    {
        // Check if hosts file is readable
        if (!is_readable($this->hostsfile)) {
            throw new HostsFileNotReadable;
        }

        // Read the hosts file
        $hosts = file_get_contents($this->hostsfile);
        $hosts = explode("\n", $hosts);

        // IP filter
        $only_for_ip = @$filters['ip'];
        // Regex filter
        $match = @$filters['match'];
        // Enabled-only filters
        $enabled_only = @$filters['enabled-only'];
        // Disabled-only filters
        $disabled_only = @$filters['disabled-only'];

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
            if ($styled and $match) {
                $host = preg_replace("/($match)/i", '<matched>$1</matched>', $host);
            }

            // Highlight disabled
            if (strncmp($ln, '#', 1) == 0) {
                if ($enabled_only) {
                    continue;
                }
                if ($styled) {
                    $host = "<comment>$host</comment>";
                }
            } elseif ($disabled_only) {
                continue;
            }

            $hosts_by_ip[trim($ip, '#')][] = $host;
        }

        return $hosts_by_ip;
    }

    protected function showBlock(OutputInterface $out, $message, $status = 'error')
    {
        if ($status != 'error') {
            if ($status == 'success') {
                $style = new OutputFormatterStyle('white', 'green');
            } elseif ($status == 'warning') {
                $style = new OutputFormatterStyle('white', 'yellow');
            } elseif ($status == 'info') {
                $style = new OutputFormatterStyle('white', 'cyan');
            }
            $status .= "-block"; // append '-block' to style to avoid overwriting styles
            $out->getFormatter()->setStyle($status, $style);
        }

        $infoMessage = (array)$message;
        $out->writeln('');
        $out->writeln($this->getHelperSet()->get('formatter')->formatBlock($infoMessage, $status, true));
        $out->writeln('');
    }
}