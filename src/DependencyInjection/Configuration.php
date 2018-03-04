<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\DependencyInjection;

use JulienDufresne\RequestId\Factory\Generator\RamseyUuidGenerator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('julien_dufresne_request_id');

        $rootNode
            ->children()
                ->append($this->addConsoleListenerNode())
                ->append($this->addRequestListenerNode())
                ->append($this->addResponseListenerNode())
                ->append($this->addGuzzleNode())
                ->append($this->addMonologNode())
                ->scalarNode('generator')
                    ->info('Service ID of the generator used to generate unique identifiers')
                    ->defaultValue(RamseyUuidGenerator::class)
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function addConsoleListenerNode()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('console_listener');

        $node
            ->addDefaultsIfNotSet()
            ->canBeDisabled();

        return $node;
    }

    private function addRequestListenerNode()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('request_listener');

        $node
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
                ->arrayNode('headers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('parent')
                        ->defaultValue('X-Parent-Request-Id')
                    ->end()
                    ->scalarNode('root')
                        ->defaultValue('X-Root-Request-Id')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function addResponseListenerNode()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('response_listener');

        $node
            ->addDefaultsIfNotSet()
            ->canBeDisabled()
            ->children()
                ->arrayNode('headers')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('current')
                        ->defaultValue('X-Current-Request-Id')
                    ->end()
                    ->scalarNode('parent')
                        ->defaultValue('X-Parent-Request-Id')
                    ->end()
                    ->scalarNode('root')
                        ->defaultValue('X-Root-Request-Id')
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function addGuzzleNode()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('guzzle');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->arrayNode('request_headers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('parent')
                            ->defaultValue('X-Parent-Request-Id')
                        ->end()
                        ->scalarNode('root')
                            ->defaultValue('X-Root-Request-Id')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }

    private function addMonologNode()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->root('monolog');

        $node
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->scalarNode('extra_entry_name')
                    ->defaultValue('request_id')
                ->end()
                ->scalarNode('current')
                    ->defaultValue('current')
                ->end()
                ->scalarNode('root')
                    ->defaultValue('root')
                ->end()
                ->scalarNode('parent')
                    ->defaultValue('parent')
                ->end()
            ->end();

        return $node;
    }
}
