<?php
namespace Utopia\MQTT;


abstract class Adapter
{
    protected string $host;
    protected int $port;
      /**
      * @var array<int|string,bool|int|string>
      */
    
    protected array $config = [];

    function __construct(string $host = '0.0.0.0', int $port = 80) {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Starts the Server.
     * @return void 
     */
    public abstract function start(): void;

    /**
     * Shuts down the Server.
     * @return void 
     */
    public abstract function shutdown(): void;

    /**
     * Sends a message to passed connections.
     * @param array<mixed,mixed> $connections Array of connection ID's.
     * @param string $message Message.
     * @return void 
     */
    public abstract function send(array $connections, string $message): void;

    /**
     * Closes a connection.
     * @param int $connection Connection ID.
     * @param int $code Close Code.
     * @return void 
     */
    public abstract function close(int $connection, int $code): void;

    /**
     * Is called when the Server starts.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onStart(callable $callback): self;

    /**
     * Is called when a Worker starts.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onWorkerStart(callable $callback): self;

    /**
     * Is called when a connection is established.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onConnect(callable $callback): self;

    /**
     * Is called when a message is received.
     * @param callable $callback 
     * @return self 
     */
    public abstract function onReceive(callable $callback): self;

    /**
     * Is called when a connection is closed.
     * @param callable $callback
     * @return self
     */
    public abstract function onClose(callable $callback): self;

    /**
     * Sets maximum package length in bytes.
     * @param int $bytes 
     * @return Adapter 
     */
    public abstract function setPackageMaxLength(int $bytes): self;

    /**
     * Sets the number of workers.
     * @param int $num 
     * @return Adapter 
     */
    public abstract function setWorkerNumber(int $num): self;

    /**
     * Returns the native server object from the Adapter.
     * @return mixed 
     */
    public abstract function getNative(): mixed;

    /**
     * Returns all connections.
     * @return array<mixed>
     */
    public abstract function getConnections(): array;

    protected function decodeValue($data): int
    {
        return 256 * ord($data[0]) + ord($data[1]);
    }

    protected function decodeString($data): string
    {
        $length = $this->decodeValue($data);
        return substr($data, 2, $length);
    }

    protected function getHeaders($data)
    {
        $byte = ord($data[0]);

        $header['type'] = ($byte & 0xF0) >> 4;
        $header['dup'] = ($byte & 0x08) >> 3;
        $header['qos'] = ($byte & 0x06) >> 1;
        $header['retain'] = $byte & 0x01;

        return $header;
    }
}