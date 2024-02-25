<?php

use PHPUnit\Framework\TestCase;
use App\Services\ApplyFiltersService;


class ServerApplyFiltersTest extends TestCase
{   
    public function testApplyFilters()
    {   
        $applyFilters = new ApplyFiltersService();

        $servers = [
            ['RAM' => 'RAM', 'RAM_TYPE' => 'RAM_TYPE', 'HDD' => 'HDD', 'HDD_TYPE' => 'HDD_TYPE', 'Location' => 'Location'],
            ['RAM' => '8GB', 'RAM_TYPE' => 'DDR4', 'HDD' => '1TB', 'HDD_TYPE' => 'SSD', 'Location' => 'New York'],
            ['RAM' => '16GB', 'RAM_TYPE' => 'DDR4', 'HDD' => '2TB', 'HDD_TYPE' => 'HDD', 'Location' => 'London'],
            ['RAM' => '32GB', 'RAM_TYPE' => 'DDR4', 'HDD' => '1TB', 'HDD_TYPE' => 'SSD', 'Location' => 'Tokyo'],
        ];

        $filterBy = [
            'ram' => ['16GB'],
            'hdd' => '2TB',
            'location' => 'London'
        ];

        $expectedServers = [
            ['RAM' => 'RAM', 'RAM_TYPE' => 'RAM_TYPE', 'HDD' => 'HDD', 'HDD_TYPE' => 'HDD_TYPE', 'Location' => 'Location'],
            ['RAM' => '16GB', 'RAM_TYPE' => 'DDR4', 'HDD' => '2TB', 'HDD_TYPE' => 'HDD', 'Location' => 'London'],
        ];

        $result = $applyFilters->applyFilters($servers, $filterBy);

        $this->assertEquals($expectedServers, $result);
    }
}
