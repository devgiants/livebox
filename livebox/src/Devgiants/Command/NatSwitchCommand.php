<?php
namespace Devgiants\Command;

use Buzz\Message\Request;
use Devgiants\Model\ApplicationCommand;
use Devgiants\Model\NatRule;
use Devgiants\Configuration\ConfigurationManager;
use Devgiants\Configuration\ApplicationConfiguration as AppConf;
use Pimple\Container;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NatSwitchCommand extends ApplicationCommand
{
	const ARGUMENT_STATUS = 'status';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';

	const ARGUMENT_ID = 'id';

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
            ->setName('nat:switch')
            ->setDescription('Switch NAT entry statuc')
			->addArgument( static::ARGUMENT_STATUS, InputArgument::REQUIRED, 'Switch status enable or disable' )
			->addArgument( static::ARGUMENT_ID, InputArgument::REQUIRED, 'Id of the NAT rule' )
            ->setHelp("This command allows you to enable/disable livebox NAT entry")
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
			$status = $input->getArgument( static::ARGUMENT_STATUS );
			if (!in_array($status, [static::STATUS_ENABLE, static::STATUS_DISABLE])) {
				throw new InvalidOptionException( "Status argument get only \"enable\" or \"disable\" value." );
			}

			$response = $this->getRunOutput(new ArrayInput([
				'command' => 'nat:infos',
			]));

			$json = json_decode($response);

			if (!empty($json->result) && !empty($json->result->status)) {
				$natRules = $json->result->status;
				$fullName = NatRule::ORIGIN . '_' . $ruleId;
				if (isset($natRules->$fullName)) {
					$natRule = NatRule::buildFrom($natRules->$fullName);

					$natRule->setEnable($status === 'enable');

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
				} else {
					throw new InvalidOptionException( "Id argument invalid, can't find it in existing NAT rules." );
				}
			} else {
				throw new LogicException('Wrong format from nat:infos command');
			}
		    // Handle post command stuff
		    parent::execute( $input, $output );
	    }
    }
}