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
    $formatHddSize = function($value) {
        preg_match('/^(\d+(?:\.\d+)?)(GB|TB)$/', $value, $matches);
        $sizeInBytes = $matches[1];
        $unit = $matches[2];

        switch ($unit) {
            case 'GB':
                return $sizeInBytes * 1000;
            case 'TB':
                return $sizeInBytes * 1000000;
            default:
                return 0;
        }
    };

    usort($filterHdd, function($a, $b) use ($formatHddSize) {
        $sizeA = $formatHddSize($a);
        $sizeB = $formatHddSize($b);

        if ($sizeA == $sizeB) {
            return 0;
        }

        return ($sizeA < $sizeB) ? -1 : 1;
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