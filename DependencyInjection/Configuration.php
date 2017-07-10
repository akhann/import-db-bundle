<?php

namespace Akhann\Bundle\ImportDbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('akhann_import_db');

        $rootNode
            ->children()
                ->arrayNode('remote_server')
                    ->children()
                        ->scalarNode('ssh_username')->end()
                        ->scalarNode('ssh_host')->end()
                        ->scalarNode('ssh_key_file')->end()
                        ->scalarNode('mysql_host')->end()
                        ->scalarNode('mysql_username')->end()
                        ->scalarNode('mysql_password')->end()
                        ->scalarNode('mysql_dbname')->end()
                        ->scalarNode('tmp_dir')->end()
                    ->end()
                ->end()
                ->arrayNode('local_server')
                    ->children()
                        //->scalarNode('ssh_username')->end()
                        //->scalarNode('ssh_host')->end()
                        //->scalarNode('ssh_key_file')->end()
                        ->scalarNode('mysql_host')->end()
                        ->scalarNode('mysql_username')->end()
                        ->scalarNode('mysql_password')->end()
                        ->scalarNode('mysql_dbname')->end()
                        ->scalarNode('tmp_dir')->end()
                    ->end()
                ->end() // twitter
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
