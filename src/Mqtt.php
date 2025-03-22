<?php

declare(strict_types=1);

namespace Hyperf\MqttClient;

use Hyperf\Context\Context;
use Hyperf\MqttClient\Exception\InvalidMqttConnectionException;
use Hyperf\MqttClient\Pool\PoolFactory;
use PhpMqtt\Client\MqttClient;

/**
 * @mixin MqttClient
 */
class Mqtt
{
    protected string $poolName = 'default';

    public function __construct(protected PoolFactory $factory)
    {
    }

    public function __call(string $name, array $arguments)
    {
        $hasContextConnection = Context::has($this->getContextKey());
        $connection           = $this->getConnection($hasContextConnection);

        try {
            $client = $connection->getConnection();
            $result = $client->{$name}(...$arguments);
        } finally {
            if (!$hasContextConnection) {
                if ($this->shouldUseSameConnection($name)) {
                    Context::set($this->getContextKey(), $connection);
                    \Swoole\Coroutine\defer(function () use ($connection) {
                        Context::set($this->getContextKey(), null);
                        $client = $connection->getConnection();
                        $client->loop(false, true);
                        $connection->release();
                    });
                } else {
                    $connection->release();
                }
            }
        }

        return $result;
    }

    private function shouldUseSameConnection(string $methodName): bool
    {
        return in_array($methodName, [
            'publish',
            'subscribe',
            'unsubscribe',
            'unsubscribe',
            'registerConnectedEventHandler',
            'registerLoopEventHandler',
            'registerMessageReceivedEventHandler',
            'registerPublishEventHandler',
            'unRegisterConnectedEventHandler',
            'unRegisterLoopEventHandler',
            'unRegisterMessageReceivedEventHandler',
            'unRegisterPublishEventHandler',
        ]);
    }

    private function getConnection($hasContextConnection): MqttConnection
    {
        $connection = null;
        if ($hasContextConnection) {
            $connection = Context::get($this->getContextKey());
        }
        if (!$connection instanceof MqttConnection) {
            $pool       = $this->factory->getPool($this->poolName);
            $connection = $pool->get();
        }
        if (!$connection instanceof MqttConnection) {
            throw new InvalidMqttConnectionException('The connection is not a valid MqttConnection.');
        }
        return $connection;
    }

    private function getContextKey(): string
    {
        return sprintf('mqtt.connection.%s', $this->poolName);
    }
}
