## 安装 ##

```bash
$ composer require lvkai/hyperf-mqtt-client
```

## 配置项 ##

| 配置项 | 类型 | 默认值 | 备注 |
| ---- | ---- | ---- | ---- |
| host | string | 'localhost' | MQTT地址 |
| port | int | 1883 | MQTT端口 |
| user_name | string | 无 | 用户名 |
| password | string | 无 | 密码 |
| keep_alive | int | 60 | 保活时间 |
| protocol | string | 3.1.1 | mqtt协议版本 |
| repository | string | \PhpMqtt\Client\Repositories\MemoryRepository::class | 仓储对象,默认内存 |
| clean_session | boole | true | 会话清除 |
| pool | object | {} | 连接池配置 |

```php
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
```

`publish`完整配置文件使用命令

```bash
$ php bin/hyperf.php vendor:publish lvkai/hyperf-mqtt-client
```

## 使用 ##

`lvkai/hyperf-mqtt-client`实现了`php-mqtt/client`代理和连接池，用户可以直接通过依赖注入`\Hyperf\Mqttclient\Mqtt`来使用mqtt客户端，实际获取的是一个`\PhpMqtt\Client\MqttClient`的一个代理对象.

```php
<?php
use Hyperf\Context\ApplicationContext;
$container = ApplicationContext::getContainer();
$mqtt = $container->get(Hyperf\MqttClient\Mqtt::class);
$mqtt->publish('helloWorld', 'test');
```
