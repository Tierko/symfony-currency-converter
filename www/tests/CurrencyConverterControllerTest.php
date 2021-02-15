<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CurrencyConverterControllerTest extends WebTestCase
{
    public function testCurrencyAll(): void
    {
        $client = static::createClient();
        $client->request('GET', '/currency/all');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($response->getContent(), true);

        $this->assertGreaterThan(0, count($data));
    }

    public function testCurrencyCalcFail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/currency/calc/3/ABC/DEF');

        $response = $client->getResponse();

        $this->assertTrue($client->getResponse()->isClientError());
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $this->assertEquals('Something went wrong. Check params', json_decode($response->getContent()));
    }

    public function testCurrencyCalcSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/currency/calc/3/GBP/AMD');

        $response = $client->getResponse();

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $this->assertIsFloat(json_decode($response->getContent()));
    }
}
