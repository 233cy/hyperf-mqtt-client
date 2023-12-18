<?php

declare(strict_types=1);

namespace Hyperf\MqttClient;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Pool\Connection;
use Hyperf\Pool\Exception\ConnectionException;
use PhpMqtt\Client\Concerns\GeneratesRandomClientIds;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Contracts\MqttClient as ClientContract;
use PhpMqtt\Client\MqttClient;
use Psr\Container\ContainerInterface;

class MqttConnection extends Connection implements ConnectionInterface
{
    use GeneratesRandomClientIds;

    protected ConnectionSettings $connectionSettings;
    protected MqttClient         $client;

    protected array $config = [
        'host'          => 'localhost',
        'port'          => 1883,
        'user_name'     => '',
        'password'      => '',
        'keep_alive'    => 60,
        'protocol'      => \PhpMqtt\Client\MqttClient::MQTT_3_1_1,
        'repository'    => \PhpMqtt\Client\Repositories\MemoryRepository::class,
        'clean_session' => true,
        'pool'          => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'waitTimeout'     => 3.0,
            'heartbeat'       => -1,
            'maxIdleTime'     => 60 * 1.5,
        ]
    ];

    public function __construct(ContainerInterface $container, protected PoolInterface $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config             = array_replace_recursive($this->config, $config);
        $this->connectionSettings = (new ConnectionSettings())->setUsername($this->config['user_name'])
            ->setPassword($this->config['password'])
            ->setKeepAliveInterval($this->config['keep_alive']);
    }

    public function getActiveConnection(): ClientContract
    {
        if ($this->check()) {
            return $this->client;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this->client;
    }

    public function reconnect(): bool
    {
        if (!isset($this->client)) {
            $clientId = $this->config['client_id'] ?? $this->generateRandomClientId();
            if (class_exists('Hyperf\Server\ServerFactory', false)) {
                $serverFactory = $this->container->get('Hyperf\Server\ServerFactory');
                $workerId      = $serverFactory->getServer()->getServer()->getWorkerId();
                $clientId      = $clientId . (is_int($workerId) ? '_' . $workerId : '') . '_' . $this->pool->getCurrentConnections();
            }

            if (!empty($this->config['repository'])) {
                $this->config['repository'] = new $this->config['repository'];
            }
            $logger       = $this->container->has(StdoutLoggerInterface::class) ? $this->container->get(StdoutLoggerInterface::class) : null;
            $this->client = new MqttClient($this->config['host'], $this->config['port'], $clientId, $this->config['protocol'], $this->config['repository'], $logger);
            $this->client->connect($this->connectionSettings, $this->config['clean_session']);
        } else {
            $this->client->connect($this->connectionSettings, $this->config['clean_session']);
        }
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        $this->client->disconnect();
        return true;
    }
}
