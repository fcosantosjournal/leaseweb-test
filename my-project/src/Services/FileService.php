<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

class FileService 
{
  public string $json;
  public string $xlsxFile;
  private array $servers;
  private string $arrayServer;

  public function __construct() {
    $this->xlsxFile = $_ENV['XLSX_PATH'];
    $this->json = $_ENV['JSON_PATH'];
  }

  public function isFileRecent(string $fileName): bool
  {
      $fileModifiedTime = filemtime($fileName);
      $currentTime = time();
      $oneHourAgo = $currentTime - ($_ENV['FILE_EXPIRATION'] * 60); 

      $result = $fileModifiedTime > $oneHourAgo;
      
      return $result;
  }

  public function generateJsonFile(): bool
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

  public function getServersFromFile(): array
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
      $hddQuantityAndSize = $this->extractFromHddQuantityAndSize($hdd);
      $server['HDD'] = $this->convertHddSize($hddQuantityAndSize['quantity'], $hddQuantityAndSize['size']);
      $server = $this->addHddTypeToServer($server, $hddQuantityAndSize['type']);
      return $server;
  }

  private function extractFromHddQuantityAndSize(string $hdd): array
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
      } elseif ($sizeInBytes > 1900 * 1000 && $sizeInBytes < 2001 * 1000) {
        return "2TB";
      } elseif ($sizeInBytes > 3900 * 1000 && $sizeInBytes < 4001 * 1000) {
        return "4TB";
      } elseif ($sizeInBytes > 7800 * 1000 && $sizeInBytes < 8001 * 1000) {
        return "8TB";
      } elseif ($sizeInBytes > 15600 * 1000 && $sizeInBytes < 16001 * 1000) {
        return "16TB";
      } elseif ($sizeInBytes > 31200 * 1000 && $sizeInBytes < 32001 * 1000) {
        return "32TB";
      } elseif ($sizeInBytes > 62400 * 1000 && $sizeInBytes < 64001 * 1000) {
        return "64TB";
      } elseif ($sizeInBytes > 124800 * 1000 && $sizeInBytes < 128001 * 1000) {
        return "128TB";
      } elseif ($sizeInBytes > 249600 * 1000 && $sizeInBytes < 256001 * 1000) {
        return "256TB";
      } elseif ($sizeInBytes > 499200 * 1000 && $sizeInBytes < 512001 * 1000) {
        return "512TB";
      } elseif ($sizeInBytes > 998400 * 1000 && $sizeInBytes < 1024001 * 1000) {
        return "1PB";
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
}