<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 22/12/17
 * Time: 14:34
 */

namespace Devgiants\Model;


use Devgiants\Exception\MissingConfigurationFileException;
use Devgiants\Service\LiveboxTools;
use Monolog\Logger;
use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;

abstract class ApplicationCommand extends Command {


	const FILE_OPTION = 'file';

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
	protected function configure() {
		$this
			->addOption( self::FILE_OPTION, "f", InputOption::VALUE_OPTIONAL, "The YML configuration file" );
	}

	/**
	 * @param InputInterface $input
	 *
	 * @return string
	 * @throws MissingConfigurationFileException
	 */
	public function getConfigurationFile( InputInterface $input ) {
		$ymlFile = $input->getOption( self::FILE_OPTION );
		if ( $ymlFile === NULL ) {
			$finder = new Finder();
			$finder
				->in( $this->container['app_dir'] )
				->files()
				->name( '*.yml' );

			if ( $finder->count() === 1 ) {
				foreach ( $finder as $file ) {
					$ymlFile = $file->getRealPath();
				}
			} else {
				throw new MissingConfigurationFileException();
			}
		}

		return $ymlFile;
	}

	/**
	 * @inheritdoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) {
		$this->tools->logout( '192.168.1.1' );
	}

	/**
	 * Run command and return the output in the current command
	 *
	 * @param ArrayInput $input
	 * @return string
	 */
	protected function getRunOutput ( ArrayInput $input ) : string {
		$application = $this->getApplication();
		$application->setAutoExit(false);
		$output = new BufferedOutput();
		$application->run($input, $output);
		$application->setAutoExit(true);
		return $output->fetch();
	}
}