<?php

namespace Devgiants\Command;

use Buzz\Message\Request;
use Devgiants\Model\ApplicationCommand;
use Devgiants\Model\NatRule;
use Devgiants\Configuration\ConfigurationManager;
use Devgiants\Configuration\ApplicationConfiguration as AppConf;
use Pimple\Container;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NatDeleteCommand extends ApplicationCommand
{

    const ARGUMENT_ID = 'id';

    /**
     * Wan command constructor.
     *
     * @param null|string $name
     * @param Container $container
     */
    public function __construct($name, Container $container)
    {
        parent::__construct($name, $container);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('nat:delete')
            ->setDescription('Delete NAT entry')
            ->addArgument(static::ARGUMENT_ID, InputArgument::REQUIRED, 'Id of the NAT rule')
            ->setHelp("This command allows you to remove livebox NAT entry");

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $ymlFile = $this->getConfigurationFile($input);

        if ($ymlFile !== NULL && is_file($ymlFile)) {

            // Structures check and configuration loading
            $configurationManager = new ConfigurationManager($ymlFile);
            $configuration = $configurationManager->load();


            // Authentication
            $this->tools->authenticate(
                $configuration[AppConf::HOST[AppConf::NODE_NAME]],
                $configuration[AppConf::USER[AppConf::NODE_NAME]],
                $configuration[AppConf::PASSWORD]
            );

            $ruleId = $input->getArgument(static::ARGUMENT_ID);

            $commandInput = new ArrayInput([
                'command' => 'nat:infos'
            ]);

            $response = $this->runAnotherCommand($commandInput, $input);
            $json = json_decode($response);

            if (!empty($json->result) && !empty($json->result->status)) {
                $natRules = $json->result->status;
                $fullName = NatRule::ORIGIN . '_' . $ruleId;
                if (isset($natRules->$fullName)) {
                    $natRule = NatRule::buildFrom($natRules->$fullName);

                    // Execute request
                    $response = $this->tools->createRequest(
                        Request::METHOD_POST,
                        "{$configuration[ AppConf::HOST[ AppConf::NODE_NAME ] ]}/ws",
                        [
                            "service" => "Firewall",
                            "method" => "deletePortForwarding",
                            "parameters" => $natRule->getOutputForDelete()
                        ]
                    );
                    $output->write($response->getContent());
                } else {
                    throw new InvalidOptionException("Id argument invalid, can't find it in existing NAT rules.");
                }
            } else {
                throw new LogicException('Wrong format from nat:infos command');
            }

            // Handle post command stuff
            parent::execute($input, $output);
        }
    }
}