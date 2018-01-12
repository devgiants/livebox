<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 18/02/17
 * Time: 07:07
 */

namespace Devgiants\Configuration;


use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class ConfigurationManager
{
    /**
     * @var string
     */
    private $configurationFile;

    public function __construct($filePath) {
        $this->configurationFile = $filePath;
    }
    /**
     * @return string
     */
    public function getConfigurationFile()
    {
        return $this->configurationFile;
    }

    /**
     * @return array
     */
    public function load() {
        // TODO check exception behavior
        $configuration = Yaml::parse(file_get_contents($this->configurationFile));

        return $this->check($configuration);
    }


    /**
     * @param $configurationUnprocessed
     * @return array
     */
    private function check($configurationUnprocessed) {
        $processor = new Processor();
        $applicationConfiguration = new ApplicationConfiguration();
        $processedConfiguration = $processor->processConfiguration($applicationConfiguration, $configurationUnprocessed);
        return $processedConfiguration;
    }
}