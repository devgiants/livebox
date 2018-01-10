<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/02/17
 * Time: 14:32
 */

namespace Devgiants\Command;

use Devgiants\Model\ApplicationCommand;
use GuzzleHttp\RequestOptions;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WifiStatusCommand extends ApplicationCommand {

	const STATUS = 'status';

	const SWITCH = 'switch';

	const ON = 'on';
	const OFF = 'off';

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
			->setDescription( 'Handle Wifi operations on Livebox' )
			->addArgument(static::STATUS,InputArgument::OPTIONAL, "Get wifi status" )
			->setHelp( "This command allows you to get wifi status" );
	}

	/**
	 * @inheritdoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {

		// Get status
		if($input->hasArgument(static::STATUS)) {
			$response = $this->tools->getClient()->post( "192.168.1.1/ws", [
				RequestOptions::HEADERS => [
					'X-Prototype-Version' => '1.7',
					'Content-Type'        => 'application/x-sah-ws-1-call+json; charset=UTF-8',
					'Accept'              => 'text/javascript'
				],
				RequestOptions::JSON    => [
					"service" => "NMC.Wifi",
					"method"  => "get",
					"parameters" => []
				]
			] );
			$json     = \GuzzleHttp\json_decode( $response->getBody()->getContents() );

			if(isset($json->result->status)) {
				$output->write($json->result->status->Status ? 1 : 0);
			}
		}

		// Handle post command stuff
		parent::execute( $input, $output );
	}
}