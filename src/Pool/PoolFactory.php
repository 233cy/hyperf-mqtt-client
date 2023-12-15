<?php

declare(strict_types=1);

namespace Hyperf\MqttClient\Pool;

use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var MqttPool[]
     */
    protected array $pools = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getPool(string $name): MqttPool
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        if ($this->container instanceof \Hyperf\Contract\ContainerInterface) {
            $pool = $this->container->make(MqttPool::class, ['name' => $name]);
        } else {
            $pool = new MqttPool($this->container, $name);
        }
        return $this->pools[$name] = $pool;
    }
}
