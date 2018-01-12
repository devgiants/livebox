<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/02/17
 * Time: 14:32
 */

namespace Devgiants\Command;

use Buzz\Message\Request;
use Devgiants\Configuration\ConfigurationManager;
use Devgiants\Configuration\ApplicationConfiguration as AppConf;
use Devgiants\Model\ApplicationCommand;
use Pimple\Container;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WifiSwitchCommand extends ApplicationCommand {

	const STATUS = 'status';

	const ON = 'on';

	const OFF = 'off';

	/**
	 * WifiSwitchCommand constructor.
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
	protected function configure() {
		$this
			->setName( 'wifi:switch' )
			->setDescription( 'Handle Wifi operations on Livebox' )
			->addArgument( static::STATUS, InputArgument::OPTIONAL, "Switch wifi on or off" )
			->setHelp( "This command allows you to enable/disable Wifi" );

		parent::configure();
	}

	/**
	 * @inheritdoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {

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

			// Get status
			if ( $input->hasArgument( static::STATUS ) ) {

				$status = $input->getArgument( static::STATUS );
				switch ( $status ) {
					case static::ON:
						$parameters = [
							"Enable" => "True",
						];
						break;
					case static::OFF:
						$parameters = [
							"Enable" => "False",
						];
						break;
					default:
						throw new InvalidOptionException( "Status argument get only \"on\" or \"off\" value." );
				}

				// Execute request
				$response = $this->tools->createRequest(
					Request::METHOD_POST,
					"{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
					[
						"service"    => "NMC.Wifi",
						"method"     => "set",
						"parameters" => $parameters,
					]
				);
				$json = json_decode( $response->getContent() );
			}


			// Handle post command stuff
			parent::execute( $input, $output );
		} else {
			$output->writeln( "<error>Filename is not correct : {$ymlFile}</error>" );
			$this->log->addError( "Filename is not correct : {$ymlFile}" );
		}

	}
}