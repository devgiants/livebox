<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 05/02/17
 * Time: 14:32
 */

namespace Devgiants\Command;

use Buzz\Browser;
use Devgiants\Model\ApplicationCommand;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\RequestOptions;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Helper\ProgressBar;
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
	}

	/**
	 * @inheritdoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {

		$token = $this->tools->authenticate( '192.168.1.1', 'admin', '5g&9i7/VzQH]' );

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

//			var_dump( \GuzzleHttp\json_encode( [
//				"service"    => "NMC.Wifi",
//				"method"     => "set",
//				"parameters" => $parameters
//			] ) );

			$response = $this->tools->getClient()->post( "192.168.1.1/ws", [
				RequestOptions::HEADERS => [
					'X-Context'           => $token,
					'X-Prototype-Version' => '1.7',
					'Content-Type'        => 'application/x-sah-ws-1-call+json; charset=UTF-8',
					'Accept'              => 'text/javascript'
				],
				RequestOptions::JSON    => [
					"service"    => "NMC.Wifi",
					"method"     => "set",
					"parameters" => $parameters
				]
			] );
			$json     = \GuzzleHttp\json_decode( $response->getBody()->getContents() );
			var_dump( $json->result );
		}


		// Handle post command stuff
		parent::execute( $input, $output );
	}
}