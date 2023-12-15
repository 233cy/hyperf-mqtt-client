<?php

declare(strict_types=1);

namespace Hyperf\MqttClient;

use PhpMqtt\Client\MqttClient;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                MqttClient::class => Mqtt::class,
            ],
            'publish'      => [
                [
                    'id'          => 'config',
                    'description' => 'The config of mqtt client.',
                    'source'      => __DIR__ . '/../publish/mqtt.php',
                    'destination' => BASE_PATH . '/config/autoload/mqtt.php',
                ],
            ],
        ];
    }
}
