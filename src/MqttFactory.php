<?php

declare(strict_types=1);

namespace Hyperf\MqttClient;

use Hyperf\Contract\ConfigInterface;
use Hyperf\MqttClient\Exception\InvalidMqttProxyException;
use function Hyperf\Support\make;

class MqttFactory
{
    /**
     * @var MqttProxy[]
     */
    protected array $proxies = [];

    public function __construct(ConfigInterface $config)
    {
        $mqttConfig = $config->get('mqtt');

        foreach ($mqttConfig as $poolName => $item) {
            $this->proxies[$poolName] = make(MqttProxy::class, ['pool' => $poolName]);
        }
    }

    public function get(string $poolName): MqttProxy
    {
        $proxy = $this->proxies[$poolName] ?? null;
        if (!$proxy instanceof MqttProxy) {
            throw new InvalidMqttProxyException('Invalid Mqtt proxy.');
        }

        return $proxy;
    }
}
