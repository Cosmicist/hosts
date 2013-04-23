<?php

namespace Hosts\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class RemoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('remove')
            ->setDescription("Remove a host from the hosts file")
            ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname you want to remove')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Remove hostname for the specified IP address')
        ;
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        // Check if hosts file is writable
        if (!is_writable($this->hostsfile)) {
            $this->error($out, "Can't write hosts file! Run the command as root.");
        }

        $hostname       = $in->getArgument('hostname');
        $ip             = $in->getOption('ip');

        // Create a styles
        $formatter = $out->getFormatter();
        $formatter->setStyle('ipaddr', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('matched', new OutputFormatterStyle('cyan', null, array('underscore')));

        $forIP = '';
        if ($ip) {
            $forIP = "for IP <info>$ip</info>";
        }

        $out->writeln("<fg=red>Removing</fg=red> host(s) matching <info>$hostname</info> $forIP");
        $out->writeln('');

        // Get matching hosts
        $hosts = $this->parseHosts(array('match' => $hostname));
        $matches = array();
        $i = 0;

        foreach ($hosts as $ip => $host_list) {
            if (!count($host_list)) {
                continue;
            }

            $out->writeln("Hosts for IP <ipaddr>$ip</ipaddr>");

            foreach ($host_list as $host) {
                $i++;
                $matches[] = $ip.'[\s\t]+'.$this->removeTags($host);
                $out->writeln(" -<question>[$i]</question> <info>$host</info>");
            }

            $out->writeln("");
        }


        // If more than 1 host is found, ask for which one to toggle
        if (count($matches) > 1) {
            $dialog = $this->getHelperSet()->get('dialog');
            $key = $dialog->askAndValidate(
                $out,
                'Select host to remove (Leave empty to cancel): ',
                function ($answer) use ($matches) {
                    $key = (int)$answer - 1;
                    if (!array_key_exists($key, $matches)) {
                        if (!$answer) {
                            return false;
                        }
                        throw new \RuntimeException("$answer is not a valid option");
                    }
                    return (int)$key;
                }
            );

            if (!$key) {
                $this->cancel($out, 'No hosts were removed');
            }

            $lineMatch = $matches[$key];
        } elseif (count($matches) == 1) {
            // Only one host found
            $lineMatch = $matches[0];
        } else {
            $this->error($out, "No hosts matching [$hostname] found");
        }

        $hostNiceName = explode('[\s\t]+', $lineMatch)[1];

        if (!$this->getHelperSet()->get('dialog')->askConfirmation(
            $out,
            "Are you sure you want to remove the host <info>$hostNiceName</info>? (y/<options=underscore>n</options=underscore>)",
            false
        )) {
            $this->cancel($out, 'No hosts were removed');
        }


        // Loop through each line and remove the matching one
        $hostsfile = explode("\n", file_get_contents($this->hostsfile));
        foreach ($hostsfile as $i => $line) {
            if (preg_match("/^#?$lineMatch\n?$/im", $line)) {
                unset($hostsfile[$i]);
            }
        }
        file_put_contents($this->hostsfile, implode("\n", $hostsfile));

        $this->success($out, "The host [$hostNiceName] was removed successfully!");
    }

    protected function removeTags($str)
    {
        return preg_replace('/<[^>]+>/i', '', $str);
    }
}
