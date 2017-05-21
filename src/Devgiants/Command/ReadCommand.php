<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/02/17
 * Time: 14:32
 */
namespace Devgiants\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    const WAN_IP = 'wan-ip';
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('read')
            ->setDescription('Read livebox data')
            ->setHelp("This command allows you to read livebox data according to passed arguments")
            ->addArgument('wan-ip', InputArgument::OPTIONAL, 'The Livebox WAN IP')
//            ->addOption(self::GUID_OPTION, "f", InputOption::VALUE_REQUIRED, "The sensor complete GUID")
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $arguments = $input->getArguments();

        // Remove first argument which is always command name
        array_shift($arguments);

        foreach($arguments as $argument) {
            switch($argument) {
                case static::WAN_IP:
                    // TODO use guzzle
                    $result = json_decode(exec('curl -s -X POST -H "Content-Type: application/x-sah-ws-1-call+json" -d \'{"service":"NMC","method":"getWANStatus","parameters":{}}\' http://192.168.1.1/ws'));
                    $output->write($result->result->data->IPAddress);
                    break;
            }
        }
    }
}