<?php

declare(strict_types=1);

namespace Hyperf\MqttClient;

use Hyperf\MqttClient\Pool\PoolFactory;
use PhpMqtt\Client\MqttClient;

/**
 * @mixin MqttClient
 */
class MqttProxy extends Mqtt
{
    public function __construct(PoolFactory $factory, string $pool)
    {
        parent::__construct($factory);

        $this->poolName = $pool;
    }

    public function __call($name, $arguments)
    {
        return parent::__call($name, $arguments);
    }
}
