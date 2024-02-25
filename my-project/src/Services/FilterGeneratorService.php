<?php

namespace App\Services;

class FilterGeneratorService
{
    public function generateFiltersByServers(array $servers): array
    {   
        
        $filters = [];
        $filtersOptions = [
            'RAM' => 'ram',
            'RAM_TYPE' => 'ram_type',
            'HDD' => 'hdd',
            'HDD_TYPE' => 'hdd_type',
            'Location' => 'location',
        ];
        foreach ($filtersOptions as $value) {
            $filters[$value] = [];
        }
        foreach ($servers as $server) {
            foreach ($filtersOptions as $key => $value) {
                if (isset($server[$key]) && !in_array($server[$key], $filters[$value])) {
                    $filters[$value][] = $server[$key];
                }
            }
        } 
        foreach ($filters as $key => $value) {
            array_shift($filters[$key]);
        }
        
        return $filters;
    }
}