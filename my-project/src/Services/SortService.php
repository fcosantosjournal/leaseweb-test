<?php

namespace App\Services;

use Exception;

class SortService 
{
  public function sortRam(array $filterRam): array
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
      
      return $sortedRam;
  }

  public function sortHdd(array $filterHdd): array
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

  public function sortLocation(array $filterLocation): array
  {     
    sort($filterLocation);
    $result = array_map(function($element) {
        $pos = preg_match('/[A-Z]{2}/', $element, $matches, PREG_OFFSET_CAPTURE);
        return $pos ? substr($element, 0, $matches[0][1]) : $element;
    }, $filterLocation);
    $filterLocation = array_values($result);
    return $filterLocation;
  } 

}