<?php
use PHPUnit\Framework\TestCase;
use WebSocket\Client as WebSocketClient;
use WebSocket\ConnectionException;

class SwooleTest extends TestCase
{
    private function getWebsocket(string $server, int $port): WebSocketClient
    {
        return new WebSocketClient('ws://'.$server.':'.$port.'/v1/realtime', [
            'timeout' => 10,
        ]);
    }

    public function setUp(): void
    {
    }

    public function testSingleConnection()
    {
        $client = $this->getWebsocket('localhost', 8001);
        $client->send('ping');
        $this->assertEquals('pong', $client->receive());

        $this->assertEquals(true, $client->isConnected());
        $client->send('disconnect');
        $this->assertEquals('disconnect', $client->receive());
        $this->expectException(ConnectionException::class);
        $client->close();
    }

    public function testMultipleConnections()
    {
        $clientA = $this->getWebsocket('localhost', 8001);
        $clientB = $this->getWebsocket('localhost', 8001);
        $clientA->send('ping');
        $this->assertEquals('pong', $clientA->receive());
        $clientB->send('pong');
        $this->assertEquals('ping', $clientB->receive());

        $clientA->send('broadcast');
        $this->assertEquals('broadcast', $clientA->receive());
        $this->assertEquals('broadcast', $clientB->receive());

        $clientB->send('broadcast');
        $this->assertEquals('broadcast', $clientA->receive());
        $this->assertEquals('broadcast', $clientB->receive());

        $clientA->close();
        $clientB->close();
    }
}
