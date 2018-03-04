<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\DependencyInjection;

use GuzzleHttp\Client as GuzzleClient;
use JulienDufresne\RequestId\Factory\RequestIdFromRequestFactory;
use JulienDufresne\RequestId\Guzzle\RequestIdMiddleware;
use JulienDufresne\RequestId\Monolog\RequestIdProcessor;
use JulienDufresne\RequestIdBundle\EventListener\ResponseListener;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class JulienDufresneRequestIdExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);
        if (null === $configuration) {
            return;
        }
        $config = $this->processConfiguration($configuration, $configs);

        if (null !== $config['generator']) {
            $container->setAlias('julien_dufresne_request_id.generator', $config['generator']);
        }

        if ($config['console_listener']['enabled']) {
            $this->configureConsoleListener($container);
        }
        if ($config['request_listener']['enabled']) {
            $this->configureRequestListener($container, $config['request_listener']);
        }
        if ($config['response_listener']['enabled']) {
            $this->configureResponseListener($container, $config['response_listener']);
        }
        if ($config['guzzle']['enabled']) {
            $this->configureGuzzle($container, $config['guzzle']);
        }

        if ($config['monolog']['enabled']) {
            $this->configureMonolog($container, $config['monolog']);
        }
    }

    private function configureConsoleListener(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('console_listener.yaml');
    }

    private function configureRequestListener(ContainerBuilder $container, array $requestListenerConfig): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('request_listener.yaml');

        $container->getDefinition(RequestIdFromRequestFactory::class)
                  ->replaceArgument(1, $requestListenerConfig['headers']['root'])
                  ->replaceArgument(2, $requestListenerConfig['headers']['parent']);
    }

    private function configureResponseListener(ContainerBuilder $container, array $responseListenerConfig): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('response_listener.yaml');

        $container->getDefinition(ResponseListener::class)
                  ->replaceArgument(1, $responseListenerConfig['headers']['current'])
                  ->replaceArgument(2, $responseListenerConfig['headers']['parent'])
                  ->replaceArgument(3, $responseListenerConfig['headers']['root']);
    }

    private function configureGuzzle(ContainerBuilder $container, array $guzzleConfig): void
    {
        if (!class_exists(GuzzleClient::class)) {
            throw new \LogicException(
                'You need the guzzlehttp/guzzle package to enable the julien_dufresne_request_id.guzzle configuration'
            );
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('guzzle.yaml');

        $container->getDefinition(RequestIdMiddleware::class)
                  ->replaceArgument(1, $guzzleConfig['request_headers']['parent'])
                  ->replaceArgument(2, $guzzleConfig['request_headers']['root']);
    }

    private function configureMonolog(ContainerBuilder $container, array $monologConfig): void
    {
        if (!class_exists(MonologBundle::class)) {
            throw new \LogicException(
                'You need the guzzlehttp/guzzle package to enable the julien_dufresne_request_id.guzzle configuration'
            );
        }

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('monolog.yaml');

        $container->getDefinition(RequestIdProcessor::class)
                  ->replaceArgument(1, $monologConfig['extra_entry_name'])
                  ->replaceArgument(2, $monologConfig['current'])
                  ->replaceArgument(3, $monologConfig['root'])
                  ->replaceArgument(4, $monologConfig['parent']);
    }
}
