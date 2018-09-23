<?php

namespace Puzzle\Admin\ContactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PuzzleAdminContactExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('admin_contact', $config);
        $container->setParameter('admin_contact.title', $config['title']);
        $container->setParameter('admin_contact.description', $config['description']);
        $container->setParameter('admin_contact.icon', $config['icon']);
        $container->setParameter('admin_contact.roles', $config['roles']);
        $container->setParameter('admin_contact.dirname', $config['dirname']);
    }
}
