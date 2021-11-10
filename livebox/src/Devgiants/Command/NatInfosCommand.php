<?php
namespace Devgiants\Command;

use Buzz\Message\Request;
use Devgiants\Model\ApplicationCommand;
use Devgiants\Configuration\ConfigurationManager;
use Devgiants\Configuration\ApplicationConfiguration as AppConf;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NatInfosCommand extends ApplicationCommand
{
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
            ->setName('nat:infos')
            ->setDescription('Read NAT infos')
            ->setHelp("This command allows you to read livebox NAR infos")
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

		    // Execute request
		    $response = $this->tools->createRequest(
			    Request::METHOD_POST,
			    "{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
			    [
				    "service"    => "Firewall",
				    "method"     => "getPortForwarding",
				    "parameters" => ['origin' => 'webui'],
			    ]
		    );
		    $output->write($response->getContent() );

		    // Handle post command stuff
		    parent::execute( $input, $output );
	    }
    }
}