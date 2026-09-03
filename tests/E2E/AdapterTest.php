<?php

declare(strict_types=1);

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;

use function Swoole\Coroutine\run;

use Utopia\WebSocket\Client;

final class AdapterTest extends TestCase
{
    private function getWebsocket(string $host, int $port): Client
    {
        return new Client('ws://' . $host . ':' . $port, [
            'timeout' => 10,
        ]);
    }

    public function testSwoole(): void
    {
        $this->testServer('127.0.0.1', 18081);
    }

    public function testWorkerman(): void
    {
        $this->testServer('127.0.0.1', 18082);
    }

    private function testServer(string $host, int $port): void
    {
        run(function () use ($host, $port): void {
            $client = $this->getWebsocket($host, $port);
            $client->connect();

            $client->send('ping');
            $this->assertSame('pong', $client->receive());
            $this->assertEquals(true, $client->isConnected());

            $clientA = $this->getWebsocket($host, $port);
            $clientA->connect();
            $clientB = $this->getWebsocket($host, $port);
            $clientB->connect();

            $clientA->send('ping');
            $this->assertSame('pong', $clientA->receive());
            $clientB->send('pong');
            $this->assertSame('ping', $clientB->receive());

            $clientA->send('broadcast');
            $this->assertSame('broadcast', $client->receive());
            $this->assertSame('broadcast', $clientA->receive());
            $this->assertSame('broadcast', $clientB->receive());

            $clientB->send('broadcast');
            $this->assertSame('broadcast', $client->receive());
            $this->assertSame('broadcast', $clientA->receive());
            $this->assertSame('broadcast', $clientB->receive());

            $clientA->close();
            $clientB->close();

            $client->send('disconnect');
            $this->assertSame('disconnect', $client->receive());

            $client->close();
            $this->assertFalse($client->isConnected());
        });
    }
}
