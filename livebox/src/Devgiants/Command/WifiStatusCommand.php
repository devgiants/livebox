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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WifiStatusCommand extends ApplicationCommand {

	const STATUS = 'status';

	/**
	 * WifiStatusCommand constructor.
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
			->setName( 'wifi:status' )
			->setDescription( 'Get wifi status (1 = ON, 0 = OFF)' )
			->setHelp( "This command allows you to get wifi status" );

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

			$response = $this->tools->createRequest(
				Request::METHOD_POST,
				"{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
				[
					"service"    => "NMC.Wifi",
					"method"     => "get",
					"parameters" => [],
				]
			);

			$json = json_decode( $response->getContent() );

			if ( isset( $json->result->status ) ) {
				$output->write( $json->result->status->Status ? 1 : 0 );
			}

			// Handle post command stuff
			parent::execute( $input, $output );
		}
	}
}