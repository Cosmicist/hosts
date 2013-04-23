<?php
/**
 * Created by JetBrains PhpStorm.
 * User: flatline
 * Date: 4/6/13
 * Time: 9:01 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Hosts\Command;


use Hosts\Command\Command;
use Hosts\Exception\HostsFileNotReadable;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ShowCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName("show")
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

        $enabled_only = $in->getOption('enabled-only');
        $disabled_only = $in->getOption('disabled-only');

        // Group hosts by IP
        try {
            $hosts_by_ip = $this->parseHosts(array(
                'ip' => $in->getOption('ip'),
                'match' => $in->getOption('match'),
                'enabled-only' => $enabled_only,
                'disabled-only' => $disabled_only
            ));
        } catch(HostsFileNotReadable $ex) {
            $this->error($out, $ex->getMessage());
        }

        // Create a styles
        $formatter = $out->getFormatter();
        $formatter->setStyle('ipaddr', new OutputFormatterStyle('cyan'));
        $formatter->setStyle('matched', new OutputFormatterStyle('cyan', null, array('underscore')));

        if ($enabled_only or $disabled_only) {
            $this->showBlock($out, "Showing [".($enabled_only ? 'en' : 'dis')."abled] hosts only", 'info');
        }

        foreach ($hosts_by_ip as $ip => $host_list) {
            if (!count($host_list)) {
                continue;
            }

            $out->writeln("Hosts for IP <ipaddr>$ip</ipaddr>");

            foreach ($host_list as $host) {
                $out->writeln(" - <info>$host</info>");
            }

            $out->writeln("");
        }

        if (!$enabled_only and !$disabled_only) {
            $out->writeln("Colors:");
            $out->writeln("* <info>enabled</info> hosts");
            $out->writeln("* <comment>disabled</comment> hosts");
        }
    }
}