<?php

namespace App\Models;

use App\Services\FileService;
use App\Services\SortService;
use App\Services\FilterGeneratorService;
use App\Services\ApplyFiltersService;

use Exception;

class ServerModel{

    private string $json;
    private $fileService;
    private $sortService;
    private $filterGeneratorService;

    public function __construct(FileService $fileService, SortService $sortService, FilterGeneratorService $filterGeneratorService) {
        $this->fileService = $fileService;
        $this->sortService = $sortService;
        $this->filterGeneratorService = $filterGeneratorService;
    }

    public function getServers(): array
    {   
        $this->json = $this->fileService->json;

        if (file_exists($this->json) && $this->fileService->isFileRecent($this->json)) {
            return $this->fileService->getServersFromFile();
        }
        try {
            if ($this->fileService->generateJsonFile()) {
                return $this->fileService->getServersFromFile();
            }
        } catch (Exception $e) {
            return $e->getMessage('File not saved!');
        }
    }

    public function getFiltersFromServers(array $servers): array
    {   
        $filters = $this->filterGeneratorService->generateFiltersByServers($servers);
        

        $filters['ram'] = $this->sortService->sortRam($filters['ram']);
        $filters['hdd'] = $this->sortService->sortHdd($filters['hdd']);
        $filters['location'] = $this->sortService->sortLocation($filters['location']);

        return $filters;
    }

    public function applyFilters(array $servers, array $filterBy): array
    {
        $applyFiltersService = new ApplyFiltersService();
        return $applyFiltersService->applyFilters($servers, $filterBy);
    }
}
