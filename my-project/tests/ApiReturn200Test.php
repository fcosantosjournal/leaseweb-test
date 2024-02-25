<?php

use PHPUnit\Framework\TestCase;

class ApiReturn200Test extends TestCase
{   
    public function testGetServersEndpointReturns200()
    {   
      $client = new \GuzzleHttp\Client();
      $response = $client->request('POST', 'http://localhost:8000/api/filter-servers');
      $this->assertEquals(200, $response->getStatusCode());
    }
}

