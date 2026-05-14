<?php

namespace Utopia\MQTT\Adapter;

use Swoole\Process;
use Swoole\Server;
use Utopia\MQTT\Adapter;
use Utopia\MQTT\MessageType;

/**
 * 
 * @package Utopia\MQTT\Adapter
 */
class Swoole extends Adapter
{
    protected Server $server;

    protected string $host;
    protected int $port;

     /**
     * @var array<int|string,array<string>>
     */
    private static array $connections = [];

    private static array $packets = [];

    public function __construct(string $host = '0.0.0.0', int $port = 80)
    {
        parent::__construct($host, $port);

        $this->server = new Server(
            $this->host,
            $this->port,
            SWOOLE_BASE
        );

        // Set maximum connections to Swoole's limit of 1 Million
        $this->config['max_connection'] = 1_000_000;
        $this->config['open_mqtt_protocol'] = true;
    }

    public function start(): void
    {
        $this->server->set($this->config);
        $this->server->start();
    }

    public function send(array $connections, string $message): void
    {
        foreach ($connections as $connection) {
            go(function () use ($connection, $message) {
                if ($this->server->exist($connection) && $this->server->isEstablished($connection)) {
                    $this->server->push($connection, $message);
                } else {
                    $this->server->close($connection);
                }
            });
        }
    }

    public function close(int $connection, int $code): void
    {
        $this->server->close($connection);
    }

    public function shutdown(): void
    {
        $this->server->shutdown();
    }

    public function onStart(callable $callback): self
    {
        $this->server->on('start', function () use ($callback) {
            $callback();

            Process::signal('2', function () {
                $this->shutdown();
            });
        });
        return $this;
    }

    public function onWorkerStart(callable $callback): self
    {
        $this->server->on('workerStart', function(Server $server, int $workerId) use ($callback) {
            $callback($workerId);
        });
        return $this;
    }

    public function onConnect(callable $callback): self
    {
        $this->server->on('connect', function (Server $server, $fd) use ($callback) {
            self::$connections[$fd] = [];

            $callback($fd);
        });
        return $this;
    }

    public function onReceive(callable $callback): self
    {
        $this->server->on('receive', function (Server $server, $fd, $fromId, $data) use ($callback) {
            echo "Received data from client #{$fd}: {$data}\n";

            $byte = \ord($data[0]);
            $type = ($byte & 0xF0) >> 4;
            $dup = ($byte & 0x08) >> 3;
            $qos = ($byte & 0x06) >> 1;
            $retain = $byte & 0x01;

            switch ($type) {
                case MessageType::CONNECT:
                    $protocolName = \substr($data, 6, (\ord($data[4]) << 8) + \ord($data[5]));
                    $clientId = \substr($data, 10 + (\ord($data[4]) << 8) + \ord($data[5]));

                    echo "Received CONNECT message from client #{$fd}:\n";
                    echo "  Protocol Name: {$protocolName}\n";
                    echo "  Client ID: {$clientId}\n";

                    if (\array_search($fd, \array_keys(self::$connections))) {
                        $this->close($fd, 1);
                    }

                    $response = \chr(0b00100000) . \chr(0b00000010); // Connack packet
                    $server->send($fd, $response);
                    break;
                case MessageType::PUBLISH:
                    $topicLength = (\ord($data[1]) << 8) + \ord($data[2]);
                    $topic = \substr($data, 3, $topicLength);
                    $payload = \substr($data, 3 + $topicLength);

                    echo "Received PUBLISH message on topic '{$topic}': {$payload}\n";

                    foreach (self::$connections as $subscriberFd => $topics) {
                        foreach ($topics as $connectedTopic) {
                            if ($topic === $connectedTopic && $subscriberFd !== $fd) {
                                $server->send($subscriberFd, $data);
                            }
                        }
                    }

                    switch ($qos) {
                        case 0:
                            break;
                        case 1:
                            $response = \chr(0b01000000) . \chr(0b00000001); // PUBACK packet
                            $server->send($fd, $response);
                            break;
                        case 2:
                            self::$packets[$fd] = $data;

                            // TODO: Add a retry loop if no PUBREL before timeout

                            $response = \chr(0b01010000) . \chr(0b00000001); // PUBREC packet

                            $server->send($fd, $response);
                    }

                    break;
                case MessageType::SUBSCRIBE:
                    $messageId = (\ord($data[2]) << 8) + \ord($data[3]);
                    $topic = \substr($data, 4);

                    echo "Received SUBSCRIBE message from client #{$fd}:\n";
                    echo "  Message ID: {$messageId}\n";
                    echo "  Topic: {$topic}\n";

                    self::$connections[$fd][] = $topic;

                    $response = \chr(0b10010000)
                        . \chr(0b00000010)
                        . \chr($data[2])
                        . \chr($data[3])
                        . \chr(0x00); // SUBACK packet

                    $server->send($fd, $response);
                    break;
                case MessageType::PINGREQ:
                    $response = \chr(0b11000000) . \chr(0b00000000); // PINGRESP packet
                    $server->send($fd, $response);
                    break;
                case MessageType::PUBREL:
                    // TODO: A PUBACK, PUBREC , PUBREL, or PUBCOMP packet MUST contain the same Packet Identifier as the PUBLISH packet that was originally sent
                    unset(self::$packets[$fd]);
                    $response = \chr(0b01110000) . \chr(0b00000001); // PUBCOMP packet
                    $server->send($fd, $response);
                    break;
                default:
                    echo "Unknown message type: {$type}\n";
            }

            $callback($fd, $fromId, $data);
        });
        return $this;
    }

    public function onClose(callable $callback): self
    {
        $this->server->on('close', function (Server $server, int $fd) use ($callback) {
            unset(self::$connections[$fd]);

            $callback($fd);
        });
        return $this;
    }

    public function setPackageMaxLength(int $bytes): self
    {
        $this->config['package_max_length'] = $bytes;
        return $this;
    }

    public function setWorkerNumber(int $num): self
    {
        $this->config['worker_num'] = $num;

        return $this;
    }

    public function getNative(): Server
    {
        return $this->server;
    }

    public function getConnections(): array
    {
        return array_keys(self::$connections);
    }
}
