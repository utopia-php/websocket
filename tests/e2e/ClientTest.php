<?php

namespace Utopia\WebSocket\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\WebSocket\Client;

class ClientTest extends TestCase
{
    private function getClient(string $host, int $port): Client
    {
        return new Client('ws://' . $host . ':' . $port, [
            'timeout' => 10,
        ]);
    }

    public function testClient(): void
    {
        $host = 'swoole';
        $port = 80;
        $client = $this->getClient($host, $port);

        $messageReceived = false;
        $client->onMessage(function ($data) use (&$messageReceived) {
            $this->assertEquals('pong', $data);
            $messageReceived = true;
        });

        $client->connect();
        $client->send('ping');

        $startTime = time();
        while (!$messageReceived && (time() - $startTime) < 10) {
            \Swoole\Event::wait();
        }

        $this->assertTrue($messageReceived);
        $this->assertTrue($client->isConnected());

        // Test multiple clients
        $clientA = $this->getClient($host, $port);
        $clientB = $this->getClient($host, $port);

        $clientAReceived = false;
        $clientBReceived = false;

        $clientA->onMessage(function ($data) use (&$clientAReceived) {
            $this->assertEquals('pong', $data);
            $clientAReceived = true;
        });

        $clientB->onMessage(function ($data) use (&$clientBReceived) {
            $this->assertEquals('ping', $data);
            $clientBReceived = true;
        });

        $clientA->connect();
        $clientB->connect();

        $clientA->send('ping');
        $clientB->send('pong');

        // Wait for responses
        \Swoole\Event::wait();
        $this->assertTrue($clientAReceived);
        $this->assertTrue($clientBReceived);

        // Test broadcast
        $broadcastCount = 0;
        $broadcastHandler = function ($data) use (&$broadcastCount) {
            $this->assertEquals('broadcast', $data);
            $broadcastCount++;
        };

        $client->onMessage($broadcastHandler);
        $clientA->onMessage($broadcastHandler);
        $clientB->onMessage($broadcastHandler);

        $startTime = time();
        $clientA->send('broadcast');
        while ($broadcastCount < 3 && (time() - $startTime) < 10) {
            \Swoole\Event::wait();
        }
        $this->assertEquals(3, $broadcastCount);

        // Test disconnection
        $clientAClosed = false;
        $clientBClosed = false;

        $clientA->onClose(function () use (&$clientAClosed) {
            $clientAClosed = true;
        });

        $clientB->onClose(function () use (&$clientBClosed) {
            $clientBClosed = true;
        });

        $clientA->close();
        $clientB->close();

        \Swoole\Event::wait();
        $this->assertTrue($clientAClosed);
        $this->assertTrue($clientBClosed);

        // Test server disconnect
        $clientDisconnected = false;
        $client->onClose(function () use (&$clientDisconnected) {
            $clientDisconnected = true;
        });

        $client->send('disconnect');
        \Swoole\Event::wait();
        $this->assertTrue($clientDisconnected);
        $this->assertFalse($client->isConnected());
    }
}
