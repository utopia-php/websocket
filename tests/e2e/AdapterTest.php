<?php

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\WebSocket\Client;

class AdapterTest extends TestCase
{
    private function getWebsocket(string $host, int $port): Client
    {
        return new Client('ws://' . $host . ':' . $port, [
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
        $client->connect();

        $client->send('ping');
        $this->assertEquals('pong', $client->receive());
        $this->assertEquals(true, $client->isConnected());

        $clientA = $this->getWebsocket($host, $port);
        $clientA->connect();
        $clientB = $this->getWebsocket($host, $port);
        $clientB->connect();

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

        try {
            $client->receive();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Failed to receive data:', $e->getMessage());
        }
    }
}
