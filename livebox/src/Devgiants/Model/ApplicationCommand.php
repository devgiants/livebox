<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 22/12/17
 * Time: 14:34
 */

namespace Devgiants\Model;


use Devgiants\Service\LiveboxTools;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ApplicationCommand extends Command {
	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * @var Logger
	 */
	protected $log;

	/**
	 * @var LiveboxTools
	 */
	protected $tools;

	/**
	 * @var string $host
	 */
	protected $host;

	/**
	 * ApplicationCommand constructor.
	 *
	 * @param null|string $name
	 * @param Container $container
	 */
	public function __construct( $name, Container $container ) {
		$this->container = $container;
		parent::__construct( $name );

		// Initiates logging
		$this->log = $this->container['main_logger'];
		if ( ! $this->log instanceof Logger ) {
			throw new \InvalidArgumentException( "Container main_logger entry must be Logger type" );
		}

		$this->tools = $this->container['tools'];
		if ( ! $this->tools instanceof LiveboxTools ) {
			throw new \InvalidArgumentException( "Container tools entry must be LiveboxTools type" );
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->tools->logout('192.168.1.1');
	}
}