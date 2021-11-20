<?php

namespace Devgiants\Command;

use Buzz\Message\Request;
use Devgiants\Model\ApplicationCommand;
use Devgiants\Model\NatRule;
use Devgiants\Configuration\ConfigurationManager;
use Devgiants\Configuration\ApplicationConfiguration as AppConf;
use Pimple\Container;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NatCreateCommand extends ApplicationCommand
{

    const OPTION_ID = 'id';
    const OPTION_PORT_INTERNAL = 'internal';
    const OPTION_PORT_EXTERNAL = 'external';
    const OPTION_IP = 'ip';
    const OPTION_PROTOCOL = 'protocol';
    const OPTION_WHITELIST = 'whitelist';

    /**
     * Wan command constructor.
     *
     * @param null|string $name
     * @param Container $container
     */
    public function __construct($name, Container $container)
    {
        parent::__construct($name, $container);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('nat:create')
            ->setDescription('Create NAT entry')
            ->addOption(static::OPTION_ID, static::OPTION_ID, InputOption::VALUE_REQUIRED, 'Id/Name of the NAT rule')
            ->addOption(static::OPTION_IP, static::OPTION_IP, InputOption::VALUE_REQUIRED, 'IP of the NAT rule')
            ->addOption(static::OPTION_PORT_EXTERNAL, static::OPTION_PORT_EXTERNAL, InputOption::VALUE_REQUIRED, 'External port of the NAT rule')
            ->addOption(static::OPTION_PORT_INTERNAL, static::OPTION_PORT_INTERNAL, InputOption::VALUE_REQUIRED, 'Internal port of the NAT rule')
            ->addOption(static::OPTION_PROTOCOL, static::OPTION_PROTOCOL, InputOption::VALUE_OPTIONAL, 'Protocol of the NAT rule: tcp, udp, both', 'tcp')
            ->addOption(static::OPTION_WHITELIST, static::OPTION_WHITELIST, InputOption::VALUE_OPTIONAL, 'IP authorized for access')
            ->setHelp("This command allows you to add livebox NAT entry to open port");

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $ymlFile = $this->getConfigurationFile($input);

        if ($ymlFile !== NULL && is_file($ymlFile)) {

            // Structures check and configuration loading
            $configurationManager = new ConfigurationManager($ymlFile);
            $configuration = $configurationManager->load();

            // Authentication
            $this->tools->authenticate(
                $configuration[AppConf::HOST[AppConf::NODE_NAME]],
                $configuration[AppConf::USER[AppConf::NODE_NAME]],
                $configuration[AppConf::PASSWORD]
            );

            $ruleId = $input->getOption(static::OPTION_ID);
            $natRule = new NatRule();
            $natRule->setId($ruleId);
            $natRule->setDescription($ruleId);
            $natRule->setDestinationIPAddress($input->getOption(static::OPTION_IP));
            $natRule->setExternalPort($input->getOption(static::OPTION_PORT_EXTERNAL));
            $natRule->setInternalPort($input->getOption(static::OPTION_PORT_INTERNAL));

            $protocolMapping = [
                'tcp' => NatRule::PROTOCOL_TCP,
                'udp' => NatRule::PROTOCOL_UDP,
                'both' => NatRule::PROTOCOL_BOTH_INTERNAL
            ];

            if ($input->hasOption(static::OPTION_PROTOCOL)) {
                $protocol = $input->getOption(static::OPTION_PROTOCOL);
                if (!in_array($protocol, array_keys($protocolMapping))) {
                    $stringProtocol = implode(' or ', array_map(function ($protocol) {
                        return '"' . $protocol . '"';
                    }, $protocolMapping));
                    throw new InvalidOptionException("Protocol argument get only " . $stringProtocol . " value.");
                }
                $natRule->setProtocol($protocolMapping[$protocol]);
            }

            if ($input->hasOption(static::OPTION_WHITELIST)) {
                $natRule->setSourcePrefix($input->getOption(static::OPTION_WHITELIST));
            }

            // Execute request
            $response = $this->tools->createRequest(
                Request::METHOD_POST,
                "{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
                [
                    "service" => "Firewall",
                    "method" => "setPortForwarding",
                    "parameters" => $natRule->getOutput()
                ]
            );
            $output->write($response->getContent());

            // Handle post command stuff
            parent::execute($input, $output);
        }
    }
}