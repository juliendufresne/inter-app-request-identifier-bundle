<?php

declare(strict_types=1);

namespace JulienDufresne\RequestIdBundle\Tests\DependencyInjection;

use JulienDufresne\RequestIdBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testProcessDefault()
    {
        $configs = [[]];

        $config = $this->process($configs);

        $this->assertArrayHasKey('generator', $config);
        $this->assertArrayHasKey('console_listener', $config);
        $this->assertArrayHasKey('request_listener', $config);
        $this->assertArrayHasKey('response_listener', $config);
        $this->assertArrayHasKey('guzzle', $config);
        $this->assertArrayHasKey('monolog', $config);
    }

    /**
     * Processes an array of configurations and returns a compiled version.
     *
     * @param array $configs An array of raw configurations
     *
     * @return array A normalized array
     */
    private function process($configs)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $configs);
    }
}
