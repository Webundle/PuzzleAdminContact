<?php

namespace Puzzle\Admin\ContactBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('puzzle_admin_contact');
        
        $rootNode
            ->children()
                ->scalarNode('title')->defaultValue('contact.title')->end()
                ->scalarNode('description')->defaultValue('contact.description')->end()
                ->scalarNode('icon')->defaultValue('contact.icon')->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('contact')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('label')->defaultValue('ROLE_CONTACT')->end()
                                ->scalarNode('description')->defaultValue('contact.role.default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('dirname')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
