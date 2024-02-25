<?php

use PHPUnit\Framework\TestCase;

class ApiReturnJsonTest extends TestCase
{   
    public function testGetServersEndpointReturnsJson()
    {   
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'http://localhost:8000/api/filter-servers');
        $contentType = $response->getHeaderLine('content-type');
        $this->assertStringContainsString('application/json', $contentType);
    }
}
