<?php
use PHPUnit\Framework\TestCase;
use MQTT\Client as MQTTClient;

class SwooleTest extends TestCase
{
    private function getMQTT(string $server, int $port): MQTTClient
    {
        return new MQTTClient('ws://'.$server.':'.$port, [
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

    private function testServer(int $port) :void
    {
        $client = $this->getMQTT('localhost', $port);
        $client->send('ping');
        $this->assertEquals('pong', $client->receive());
        $this->assertEquals(true, $client->isConnected());

        $clientA = $this->getMQTT('localhost', $port);
        $clientB = $this->getMQTT('localhost', $port);

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
