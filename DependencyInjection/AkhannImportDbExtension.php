<?php

namespace Akhann\Bundle\ImportDbBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AkhannImportDbExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        foreach ($config['remote_server'] as $key => $crs) {
            $container->setParameter('akhann_import_db.remote_server.'.$key, $crs);
        }

        foreach ($config['local_server'] as $key => $crs) {
            $container->setParameter('akhann_import_db.local_server.'.$key, $crs);
        }
    }
}
