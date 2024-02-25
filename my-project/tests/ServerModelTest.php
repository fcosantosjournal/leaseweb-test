<?php
use PHPUnit\Framework\TestCase;
use App\Services\FilterGeneratorService;

class ServerModelTest extends TestCase
{   
    public function testGenerateFiltersByServers()
    {
        $servers = [
          ['RAM' => 'RAM', 'RAM_TYPE' => 'RAM_TYPE', 'HDD' => 'HDD', 'HDD_TYPE' => 'HDD_TYPE', 'Location' => 'Location'],
          ['RAM' => '8GB', 'RAM_TYPE' => 'DDR4', 'HDD' => '500GB', 'HDD_TYPE' => 'SSD', 'Location' => 'Location A'],
          ['RAM' => '16GB', 'RAM_TYPE' => 'DDR3', 'HDD' => '1TB', 'HDD_TYPE' => 'HDD', 'Location' => 'Location B'],
        ];
        
        $filtersService = new FilterGeneratorService();
        $filters = $filtersService->generateFiltersByServers($servers);

        $this->assertEquals([
          'ram' => ['8GB', '16GB'],
          'ram_type' => ['DDR4', 'DDR3'],
          'hdd' => ['500GB', '1TB'],
          'hdd_type' => ['SSD', 'HDD'],
          'location' => ['Location A', 'Location B'],
        ], $filters);        
    }
}
