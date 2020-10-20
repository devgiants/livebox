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
use Symfony\Component\Console\Output\OutputInterface;

class NatCreateCommand extends ApplicationCommand
{

	const ARGUMENT_ID = 'id';
	const ARGUMENT_PORT_INTERNAL = 'internal';
	const ARGUMENT_PORT_EXTERNAL = 'external';
	const ARGUMENT_IP = 'ip';
	const ARGUMENT_PROTOCOL = 'protocol';

	/**
	 * Wan command constructor.
	 *
	 * @param null|string $name
	 * @param Container $container
	 */
	public function __construct( $name, Container $container ) {
		parent::__construct( $name, $container );
	}

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('nat:create')
            ->setDescription('Create NAT entry')
			->addArgument( static::ARGUMENT_ID, InputArgument::REQUIRED, 'Id/Name of the NAT rule' )
			->addArgument( static::ARGUMENT_IP, InputArgument::REQUIRED, 'IP of the NAT rule' )
			->addArgument( static::ARGUMENT_PORT_EXTERNAL, InputArgument::REQUIRED, 'Internal port of the NAT rule' )
			->addArgument( static::ARGUMENT_PORT_INTERNAL, InputArgument::REQUIRED, 'External port of the NAT rule' )
			->addArgument( static::ARGUMENT_PROTOCOL, InputArgument::OPTIONAL, 'Protocol of the NAT rule: tcp, udp, both' )
            ->setHelp("This command allows you to add livebox NAT entry to open port")
        ;

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

	    $ymlFile = $this->getConfigurationFile( $input );

	    if ( $ymlFile !== NULL && is_file( $ymlFile ) ) {

		    // Structures check and configuration loading
		    $configurationManager = new ConfigurationManager( $ymlFile );
		    $configuration        = $configurationManager->load();

			// Authentication
			$this->tools->authenticate(
				$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ],
				$configuration[ AppConf::USER[ AppConf::NODE_NAME ] ],
				$configuration[ AppConf::PASSWORD ]
			);

			$ruleId = $input->getArgument( static::ARGUMENT_ID );
			$natRule = new NatRule();
			$natRule->setId($ruleId);
			$natRule->setDescription($ruleId);
			$natRule->setDestinationIPAddress($input->getArgument( static::ARGUMENT_IP ));
			$natRule->setExternalPort($input->getArgument( static::ARGUMENT_PORT_EXTERNAL ));
			$natRule->setInternalPort($input->getArgument( static::ARGUMENT_PORT_INTERNAL ));

			$protocolMapping = [
				'tcp' => NatRule::PROTOCOL_TCP,
				'udp' => NatRule::PROTOCOL_UDP,
				'both' => NatRule::PROTOCOL_BOTH_INTERNAL
			];
			$protocol = $input->getArgument( static::ARGUMENT_PROTOCOL );
			if (!empty($protocol)) {
				if (!in_array($protocol, array_keys($protocolMapping))) {
					$stringProtocol = implode(' or ', array_map(function ($protocol) {
						return '"'.$protocol.'"';
					}, $protocolMapping));
					throw new InvalidOptionException( "Protocol argument get only ".$stringProtocol." value." );
				}
				$natRule->setProtocol($protocolMapping[$protocol]);
			}


			// Execute request
			$response = $this->tools->createRequest(
				Request::METHOD_POST,
				"{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
				[
					"service"    => "Firewall",
					"method"     => "setPortForwarding",
					"parameters" => $natRule->getOutput()
				]
			);
			$output->write($response->getContent() );

		    // Handle post command stuff
		    parent::execute( $input, $output );
	    }
    }
}