<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'default' => [
        'host'          => env('MQTT_HOST', 'localhost'),
        'port'          => (int)env('MQTT_PORT', 1883),
        'client_id'     => env('MQTT_CLIENT_ID', ''),
        'user_name'     => env('MQTT_USERNAME', ''),
        'password'      => env('MQTT_PASSWORD', ''),
        'keep_alive'    => env('MQTT_KEEP_ALIVE', 20),
        'protocol'      => \PhpMqtt\Client\MqttClient::MQTT_3_1_1,
        'repository'    => \PhpMqtt\Client\Repositories\MemoryRepository::class,
        'clean_session' => true,
        'pool'          => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'waitTimeout'     => 3.0,
            'heartbeat'       => -1,
            'maxIdleTime'     => env('MQTT_KEEP_ALIVE', 20) * 1.5,
        ]
    ]
];
