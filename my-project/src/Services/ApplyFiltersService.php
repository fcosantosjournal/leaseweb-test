<?php

namespace App\Services;

class ApplyFiltersService 
{
    public function applyFilters(array $servers, array $filterBy): array
    {   
        if (empty(array_filter($filterBy))) {
            return $servers;
        } 

        $firstServer = $servers[0];

        foreach ($servers as $key => $server) {
            if (!$this->matchesFilters($server, $filterBy)) {
                unset($servers[$key]);
            }
        }

        array_unshift($servers, $firstServer);
        
        return $servers;
    }

    private function matchesFilters(array $server, array $filterBy): bool
    {
        foreach ($filterBy as $key => $value) {
            if (empty($value)) {
                continue; 
            }
            
            switch ($key) {
                case 'hdd':
                    if ($server['HDD'] !=  $value) {
                        return false;
                    }
                    break;
                case 'ram_type':
                    if ($server['RAM_TYPE'] != $value) {
                        return false;
                    }
                    break;
                case 'hdd_type':
                    if ($server['HDD_TYPE'] != $value) {
                        return false;
                    }
                    break;
                case 'ram':
                    if (!in_array($server['RAM'], $value)) {
                        return false;
                    }
                    break;
                case 'location':
                    if ($this->cleanLocationValue($server['Location']) != $value) {
                        return false;
                    }
                    break;
                default:
                    // Ignone unknown filters
                    break;
            }
        }
        return true;
    }

    private function cleanLocationValue(string $location): string
    {
        $pos = preg_match('/[A-Z]{2}/', $location, $matches, PREG_OFFSET_CAPTURE);
        return $pos ? substr($location, 0, $matches[0][1]) : $location;
    }
}
