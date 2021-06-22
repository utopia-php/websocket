<?php
use PHPUnit\Framework\TestCase;
use WebSocket\Client as WebSocketClient;
use WebSocket\ConnectionException;
use WebSocket\TimeoutException;

class WorkermanTest extends TestCase
{
    private function getWebsocket(string $server, int $port): WebSocketClient
    {
        return new WebSocketClient('ws://'.$server.':'.$port.'/v1/realtime', [
            'timeout' => 5,
        ]);
    }

    public function setUp(): void
    {
    }

    public function testSingleConnection()
    {
        $client = $this->getWebsocket('localhost', 8002);
        $client->send('ping');
        $this->assertEquals('pong', $client->receive());

        $this->assertEquals(true, $client->isConnected());
        $client->send('disconnect');
        $this->assertEquals('disconnect', $client->receive());
        $this->expectException(Throwable::class);
        $client->receive();
    }

    public function testMultipleConnections()
    {
        $clientA = $this->getWebsocket('localhost', 8002);
        $clientB = $this->getWebsocket('localhost', 8002);
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
