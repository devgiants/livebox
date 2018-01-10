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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WanCommand extends ApplicationCommand
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
            ->setName('wan')
            ->setDescription('Read WAN IP')
            ->setHelp("This command allows you to read livebox WAN IP")

        ;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

	    $response = $this->tools->getClient()->post( "192.168.1.1/ws", [
		    RequestOptions::HEADERS => [
			    'Content-Type' => 'application/x-sah-ws-1-call+json'
		    ],
		    RequestOptions::JSON => [
			    "service" => "NMC",
			    "method" => "getWANStatus",
	            "parameters" => []
		    ]
	    ]);
	    $json     = \GuzzleHttp\json_decode( $response->getBody()->getContents() );

	    var_dump($json);

        // Handle post command stuff
        parent::execute($input, $output);
    }
}