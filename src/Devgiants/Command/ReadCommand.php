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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReadCommand extends Command
{
    const GUID_OPTION = 'guid';
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('read')
            ->setDescription('Read 1-wire sensor data according to GUID')
            ->setHelp("This command allows you to 1-wire sensor value. ")
            ->addOption(self::GUID_OPTION, "f", InputOption::VALUE_REQUIRED, "The sensor complete GUID")
        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO check GUID syntax
        // Get GUID
        $guid = $input->getOption(self::GUID_OPTION);
        $value = "";

        // Remove hexa data
        $data = explode(' ', exec("cat /sys/bus/w1/devices/$guid/w1_slave"));
        $value = array_pop($data);

        // Remove "t=" or whatever
        $value = explode('=', $value);
        $output->write(array_pop($value));


    }
}