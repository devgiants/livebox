<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 18/02/17
 * Time: 07:26
 */

namespace Devgiants\Configuration;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ApplicationConfiguration implements ConfigurationInterface
{
    const ROOT_NODE = 'configuration';
    const NODE_NAME = 'name';
    const NODE_DEFAULT_VALUE = 'default_value';

    const HOST = [
        self::NODE_NAME => 'host',
        self::NODE_DEFAULT_VALUE => '192.168.1.1'
    ];

	const USER = [
		self::NODE_NAME => 'user',
		self::NODE_DEFAULT_VALUE => 'admin'
	];

    const PASSWORD = "password";


    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(static::ROOT_NODE);

        // add node definitions to the root of the tree
        $rootNode
            ->children()
                ->scalarNode(static::HOST[static::NODE_NAME])
                    ->defaultValue(static::HOST[static::NODE_DEFAULT_VALUE])
                    ->info('Contains the Livebox IP address or local DNS name. Default to 192.168.1.1')
                ->end()
		        ->scalarNode(static::USER[static::NODE_NAME])
			        ->defaultValue(static::USER[static::NODE_DEFAULT_VALUE])
			        ->info('Contains user for connecting. Default to admin')
		        ->end()
		        ->scalarNode(static::PASSWORD)
			        ->info('Contains password for connecting')
			        ->isRequired()
			        ->cannotBeEmpty()
		        ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}