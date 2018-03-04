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
use Symfony\Component\Console\ConsoleEvents;
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

        if ($config['console_listener']['enabled'] && class_exists(ConsoleEvents::class)) {
            $loader->load('console_listener.yaml');
        }

        if ($config['request_listener']['enabled']) {
            $loader->load('request_listener.yaml');
            $this->configureRequestListener($container, $config['request_listener']);
        }

        if ($config['response_listener']['enabled']) {
            $loader->load('response_listener.yaml');
            $this->configureResponseListener($container, $config['response_listener']);
        }

        if ($config['guzzle']['enabled'] && class_exists(GuzzleClient::class)) {
            $loader->load('guzzle.yaml');
            $this->configureGuzzle($container, $config['guzzle']);
        }

        if ($config['monolog']['enabled'] && class_exists(MonologBundle::class)) {
            $loader->load('monolog.yaml');
            $this->configureMonolog($container, $config['monolog']);
        }
    }

    private function configureRequestListener(ContainerBuilder $container, array $requestListenerConfig): void
    {
        $container->getDefinition(RequestIdFromRequestFactory::class)
                  ->replaceArgument(1, $requestListenerConfig['headers']['root'])
                  ->replaceArgument(2, $requestListenerConfig['headers']['parent']);
    }

    private function configureResponseListener(ContainerBuilder $container, array $responseListenerConfig): void
    {
        $container->getDefinition(ResponseListener::class)
                  ->replaceArgument(1, $responseListenerConfig['headers']['current'])
                  ->replaceArgument(2, $responseListenerConfig['headers']['parent'])
                  ->replaceArgument(3, $responseListenerConfig['headers']['root']);
    }

    private function configureGuzzle(ContainerBuilder $container, array $guzzleConfig): void
    {
        $container->getDefinition(RequestIdMiddleware::class)
                  ->replaceArgument(1, $guzzleConfig['request_headers']['parent'])
                  ->replaceArgument(2, $guzzleConfig['request_headers']['root']);
    }

    private function configureMonolog(ContainerBuilder $container, array $monologConfig): void
    {
        $container->getDefinition(RequestIdProcessor::class)
                  ->replaceArgument(1, $monologConfig['extra_entry_name'])
                  ->replaceArgument(2, $monologConfig['current'])
                  ->replaceArgument(3, $monologConfig['root'])
                  ->replaceArgument(4, $monologConfig['parent']);
    }
}
