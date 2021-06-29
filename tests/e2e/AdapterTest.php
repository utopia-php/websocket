<?php
use PHPUnit\Framework\TestCase;
use WebSocket\Client as WebSocketClient;

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

    public function testSwoole(): void
    {
        $this->testServer(8001);
    }

    public function testWorkerman(): void
    {
        $this->testServer(8002);
    }

    private function testServer(int $port)
    {
        $client = $this->getWebsocket('localhost', $port);
        $client->send('ping');
        $this->assertEquals('pong', $client->receive());
        $this->assertEquals(true, $client->isConnected());

        $clientA = $this->getWebsocket('localhost', $port);
        $clientB = $this->getWebsocket('localhost', $port);

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
        $this->expectException(Throwable::class);
        $client->receive();
    }
}
