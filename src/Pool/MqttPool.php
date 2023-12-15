<?php

declare(strict_types=1);

namespace Hyperf\MqttClient\Pool;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\MqttClient\MqttConnection;
use Hyperf\Pool\Frequency;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;
use function Hyperf\Support\make;

class MqttPool extends Pool
{
    protected array $config;

    public function __construct(ContainerInterface $container, protected string $name)
    {
        $config = $container->get(ConfigInterface::class);
        $key    = sprintf('mqtt.%s', $this->name);
        if (!$config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $this->config = $config->get($key);
        $options      = Arr::get($this->config, 'pool');

        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function createConnection(): ConnectionInterface
    {
        return new MqttConnection($this->container, $this, $this->config);
    }
}
