<?php

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;
use WebSocket\Client as WebSocketClient;

class AdapterTest extends TestCase
{
    private function getWebsocket(string $host, int $port): WebSocketClient
    {
        return new WebSocketClient('ws://' . $host . ':' . $port, [
            'timeout' => 10,
        ]);
    }

    public function setUp(): void
    {
    }

    public function testSwoole(): void
    {
        $this->testServer('swoole', 80);
    }

    public function testWorkerman(): void
    {
        $this->testServer('workerman', 80);
    }

    private function testServer(string $host, int $port): void
    {
        $client = $this->getWebsocket($host, $port);
        $client->send('ping');
        $this->assertEquals('pong', $client->receive());
        $this->assertEquals(true, $client->isConnected());

        $clientA = $this->getWebsocket($host, $port);
        $clientB = $this->getWebsocket($host, $port);

        $clientA->send('ping');
        $this->assertEquals('pong', $clientA->receive());
        $clientB->send('pong');
        $this->assertEquals('ping', $clientB->receive());

        $clientA->send('broadcast');
        $this->assertEquals('broadcast', $client->receive());
        $this->assertEquals('broadcast', $clientA->receive());
        $this->assertEquals('broadcast', $clientB->receive());

        $clientB->send('broadcast');
        $this->assertEquals('broadcast', $client->receive());
        $this->assertEquals('broadcast', $clientA->receive());
        $this->assertEquals('broadcast', $clientB->receive());

        $clientA->close();
        $clientB->close();

        $client->send('disconnect');
        $this->assertEquals('disconnect', $client->receive());
        $this->expectException(\Throwable::class);
        $client->receive();
    }
}
