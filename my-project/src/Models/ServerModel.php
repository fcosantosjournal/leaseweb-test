<?php

namespace App\Models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class ServerModel{

    private string $json;
    private string $xlsxFile;
    private array $servers;
    private string $arrayServer;

    public function __construct() {
        $this->xlsxFile = $_ENV['XLSX_PATH'];
        $this->json = $_ENV['JSON_PATH'];
    }

    public function getServers(): array
    {   
        if (file_exists($this->json) && $this->isFileRecent($this->json)) {
            return $this->getServersFromFile();
        }

        try {
            if ($this->generateJsonFile()) {
                return $this->getServersFromFile();
            }
        } catch (Exception $e) {
            return $e->getMessage('File not saved!');
        }
    }

    private function isFileRecent(string $fileName): bool
    {
        $fileModifiedTime = filemtime($fileName);
        $currentTime = time();
        $oneHourAgo = $currentTime - ($_ENV['FILE_EXPIRATION'] * 60); 

        $result = $fileModifiedTime > $oneHourAgo;
        
        return $result;
    }

    private function generateJsonFile(): bool
    {
        $spreadsheet = IOFactory::load($this->xlsxFile);
        $sheet = $spreadsheet->getActiveSheet();

        $data = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rowData = new \stdClass();
            $columnMap = [
                'A' => 'Model',
                'B' => 'RAM',
                'C' => 'HDD',
                'D' => 'Location',
                'E' => 'Price'
            ];
            foreach ($row->getCellIterator() as $cell) {
                $value = $cell->getValue();
                $column = $cell->getColumn();
                if (isset($columnMap[$column])) {
                    $rowData->{$columnMap[$column]} = $value;
                }
            }
            $data[] = $rowData;
        }

        $jsonData = json_encode($data);

        file_put_contents($this->json, $jsonData);

        if (file_exists($this->json)) {
            return true;
        }
        return false;
    }

    private function getServersFromFile(): array
    {
        $this->arrayServer = file_get_contents($this->json);
        $this->servers = json_decode($this->arrayServer, true);
        $this->servers = $this->splitRamInQuantityAndType($this->servers);
        
        $this->servers = $this->splitHddInQuantitySizeAndType($this->servers);
        
        return $this->servers;
    }

    public function splitRamInQuantityAndType(array $servers): array
    {   
        foreach ($servers as $key => $server) {
            if ($key === 0) {
                $servers[$key] = array_merge(['RAM_TYPE' => 'RAM TYPE'], $server);
            } else {
                $ram = explode('GB', $server['RAM']);
                $servers[$key]['RAM'] = $ram[0] . "GB";
                $servers[$key]['RAM_TYPE'] = $ram[1];
            }
        }
        return $servers;
    }
    

    public function splitHddInQuantitySizeAndType(array $servers): array
    {
        for ($i = 0; $i < count($servers); $i++) {
            if ($i == 0) {
                $servers[$i] = $this->addHddTypeHeader($servers[$i]);
            } else {
                $servers[$i] = $this->splitHddDetails($servers[$i]);
            }
        }

        return $servers;
    }

    private function addHddTypeHeader(array $server): array
    {   
        $key = 'HDD_TYPE';
        $value = 'HDD TYPE';
        $server = array_merge(array_slice($server, 0, 4), array($key => $value), array_slice($server, 4));
        return $server;
    }

    private function splitHddDetails(array $server): array
    {
        $hdd = $server['HDD'];
        $hddQuantityAndSize = $this->extractHddQuantityAndSize($hdd);
        $server['HDD'] = $this->convertHddSize($hddQuantityAndSize['quantity'], $hddQuantityAndSize['size']);
        $server = $this->addHddTypeToServer($server, $hddQuantityAndSize['type']);
        return $server;
    }

    private function extractHddQuantityAndSize(string $hdd): array
    {
        $hddQuantityAndSize = explode('x', $hdd);
        $sizeAndType = explode('B', $hddQuantityAndSize[1]);
        $size = $sizeAndType[0];
        $type = trim($sizeAndType[1]);
        return ['quantity' => $hddQuantityAndSize[0], 'size' => $size, 'type' => $type];
    }

    private function convertHddSize($quantity, $size): string
    {
        $sizeInBytes = $this->getSizeInBytes($quantity, $size);
        $formattedSize = $this->formatHddSize($sizeInBytes);

        if (strpos($size, 'G') !== false && strpos($formattedSize, '.') !== false) {
            $decimalPart = substr($formattedSize, strpos($formattedSize, '.') + 1);
            if ((int)$decimalPart >= 8) {
                $formattedSize = (int)$formattedSize + 1 . "TB";
            }
        }

        return $formattedSize;
    }

    private function getSizeInBytes($quantity, $size): int
    {
        if (strpos($size, 'G') !== false) {
            $size = rtrim($size, 'G');
            $size = ($size < 1000) ? $size : $size / 1000;
        } else {
            $size = rtrim($size, 'T') * 1000;
        }
        return $quantity * $size;
    }

    private function formatHddSize(int $sizeInBytes): string
    {
        if ($sizeInBytes == 240 * 1000) {
            return "250GB";
        } elseif ($sizeInBytes == 480 * 1000) {
            return "500GB";
        } elseif ($sizeInBytes < 1000) {
            return $sizeInBytes . "GB";
        } else {
            return ($sizeInBytes / 1000) . "TB";
        }
    }

    private function addHddTypeToServer(array $server, string $type): array
    {   
        $newKey = 'HDD_TYPE';
        $newVal = $type;
        $server = array_merge(array_slice($server, 0, 4), array($newKey => $newVal), array_slice($server, 4));
        return $server;
    }


    public function getFiltersFromServers(array $servers): array
    {
        $filters = [];

        $filtersOptions = [
            'RAM' => 'ram',
            'RAM_TYPE' => 'ram_type',
            'HDD' => 'hdd',
            'HDD_TYPE' => 'hdd_type',
            'Location' => 'location',
        ];

        // Initialize filters
        foreach ($filtersOptions as $value) {
            $filters[$value] = [];
        }
        
        foreach ($servers as $server) {
            foreach ($filtersOptions as $key => $value) {
                // Add unique values to filters
                if (isset($server[$key]) && !in_array($server[$key], $filters[$value])) {
                    $filters[$value][] = $server[$key];
                }
            }
        }        

        // Order Ram
        $filters['ram'] = $this->sortRam($filters['ram']);

        // Order Hdd
        $filters['hdd'] = $this->sortHdd($filters['hdd']);
        
        // Order Location
        $filters['location'] = $this->sortLocation($filters['location']);
        
        return $filters;
    }


    private function sortRam(array $filterRam): array
    {    
        $ramValues = [];

        $firstItem = $filterRam[0];
        if ($firstItem === 'RAM') {
            array_shift($filterRam);
        }

        foreach ($filterRam as $ram) {
            preg_match('/(\d+)(\s*(?:GB|TB))/', $ram, $matches);
            $ramValues[] = [
                'value' => (int)$matches[1],
                'unit' => trim($matches[2])
            ];
        }
        
        usort($ramValues, function($a, $b) {
            if ($a['unit'] !== $b['unit']) {
                return $a['unit'] === 'GB' ? -1 : 1;
            } else {
                return $a['value'] - $b['value'];
            }
        });
        
        $sortedRam = [];
        foreach ($ramValues as $ram) {
            $sortedRam[] = $ram['value'] . $ram['unit'];
        }

        array_unshift($sortedRam, 'RAM');
        
        return $sortedRam;
    }


    private function sortHdd(array $filterHdd): array
    {    
        array_shift($filterHdd);
        $extractData = function($value) {
            preg_match('/^(\d+(?:\.\d+)?)(GB|TB)$/', $value, $matches);
            return [
                'value' => (float)$matches[1],
                'unit' => $matches[2]
            ];
        };
        usort($filterHdd, function($a, $b) use ($extractData) {
            $dataA = $extractData($a);
            $dataB = $extractData($b);
            if ($dataA['unit'] !== $dataB['unit']) {
                return strcmp($dataA['unit'], $dataB['unit']);
            } else {
                return $dataA['value'] - $dataB['value'];
            }
        });
        return $filterHdd;
    }

    private function sortLocation(array $filterLocation): array
    {
        sort($filterLocation);
        $result = array_map(function($element) {
            $pos = preg_match('/[A-Z]{2}/', $element, $matches, PREG_OFFSET_CAPTURE);
            return $pos ? substr($element, 0, $matches[0][1]) : $element;
        }, $filterLocation);
        $filterLocation = array_values($result);
        return $filterLocation;
    }  

    public function applyFilters(array $servers, array $filterBy): array
    {
        if (empty($filterBy)) {
            return $servers;
        }

        $keys = array_keys($servers[1]);
        $keys = array_combine($keys, $keys);

        if (empty($servers[0])) {
            array_shift($servers);
        }
        
        foreach ($servers as $key => $server) {
            if (isset($filterBy['ram']) && $filterBy['ram'] != "") {
                if ($server['RAM'] != $filterBy['ram']) {
                    unset($servers[$key]);
                }
            }
            if (isset($filterBy['ram_type']) && $filterBy['ram_type'] != "") {
                if ($server['RAM_TYPE'] != $filterBy['ram_type']) {
                    unset($servers[$key]);
                }
            }

            if (isset($filterBy['hdd']) && $filterBy['hdd'] != "") {
                if ($server['HDD'] != $filterBy['hdd']) {
                    unset($servers[$key]);
                }
            }

            if (isset($filterBy['hdd_type']) && $filterBy['hdd_type'] != "") {
                if ($server['HDD_TYPE'] != $filterBy['hdd_type']) {
                    unset($servers[$key]);
                }
            }

            if (isset($filterBy['location']) && $filterBy['location'] != "") {
                $cleanFilterLocation = $this->cleanLocationValue($server['Location']);
                
                if ($cleanFilterLocation != $filterBy['location']) {
                    unset($servers[$key]);
                }
            }
        }
        array_unshift($servers, $keys);
        return $servers;
    }

    private function cleanLocationValue(string $location): string
    {
        $pos = preg_match('/[A-Z]{2}/', $location, $matches, PREG_OFFSET_CAPTURE);
        return $pos ? substr($location, 0, $matches[0][1]) : $location;
    }
}
