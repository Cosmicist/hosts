<?php

namespace Hosts\Command;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ToggleCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('toggle')
            ->setDescription("Enable/Disable a host")
            ->addArgument('hostname', InputArgument::REQUIRED, 'The hostname you want to toggle')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, 'Toggle hostname for the specified IP address')
            ->addOption('force-enable', 'e', InputOption::VALUE_NONE, 'Force enable host')
            ->addOption('force-disable', 'd', InputOption::VALUE_NONE, 'Force disable host')
        ;
    }

    protected function execute(InputInterface $in, OutputInterface $out)
    {
        parent::execute($in, $out);

        // Check if hosts file is writable
        if (!is_writable($this->hostsfile)) {
            $this->showBlock($out, "Can't write hosts file! Run the command as root.");
            exit;
        }

        $hostname       = $in->getArgument('hostname');
        $ip             = $in->getOption('ip');
        $forceEnable    = $in->getOption('force-enable');
        $forceDisable   = $in->getOption('force-disable');

        // Create a styles
        $formatter = $out->getFormatter();
        $formatter->setStyle('ipaddr', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('matched', new OutputFormatterStyle('cyan', null, array('underscore')));

        if ($forceEnable) {
            $mode = 'Enable';
        } elseif ($forceDisable) {
            $mode = 'Disable';
        } else {
            $mode = 'Toggle';
        }

        $forIP = '';
        if ($ip) {
            $forIP = "for IP <info>$ip</info>";
        }

        $out->writeln("<comment>$mode</comment> host <info>$hostname</info> $forIP");
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
                'Which of the matched hosts do you want to '.strtolower($mode).'? ',
                function ($answer) use ($matches) {
                    $key = (int)$answer - 1;
                    if (!array_key_exists($key, $matches)) {
                        throw new \RuntimeException("$answer is not a valid option");
                    }
                    return (int)$key;
                }
            );

            $lineMatch = $matches[$key];
        } else {
            // Only one host found
            $lineMatch = $matches[0];
        }

        $hostsfile = file_get_contents($this->hostsfile);
        $hostsfile = preg_replace_callback(
            "/#?$lineMatch/i",
            function ($matches) use (&$action, $forceEnable, $forceDisable) {
                // @todo Force enable/disable!!
                if (!$forceDisable and ($forceEnable or preg_match('/^#/', $matches[0]))) {
                    $rs = preg_replace('/^#/', '', $matches[0]);
                    $action = 'enabled';
                } else {
                    $rs = $matches[0];
                    // Only add # if there isn't one already
                    if (!preg_match('/^#/', $rs)) {
                        $rs = "#{$rs}";
                    }
                    $action = 'disabled';
                }

                return $rs;
            },
            $hostsfile
        );

        $this->showBlock($out, "The host was [$action] successfully!", 'success');

        file_put_contents($this->hostsfile, $hostsfile);

        $out->writeln('');
    }

    protected function removeTags($str)
    {
        return preg_replace('/<[^>]+>/i', '', $str);
    }
}
