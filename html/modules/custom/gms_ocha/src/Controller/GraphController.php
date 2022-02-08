<?php

namespace Drupal\gms_ocha\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Get the Sankeychart Data.
 */
class GraphController extends ControllerBase {

  /**
   * Get the Sankeychart Data.
   */
  public function getJsonData($year = 2016, $country = NULL, $donor = NULL) {
    $cached = $this->cache()->get('Poolfund_project_sankey_chart_' . $year);
    $data = $cached->data ?? '';
    if ($data) {
      $colors = ['color1', 'color4', 'color2', 'color3', 'color5', 'color6'];
      $nodes = $node_check = $links = [];
      $i = $j = 0;
      if ($country != 'select') {
        $rows[] = $data[$country];
      }
      else {
        $rows = $data;
      }

      foreach ($rows as $value) {
        if ($i == 5) {
          $i = 0;
        }

        $data_donor = [];
        if ($donor != 'select') {
          if (array_key_exists($donor, $value['donor'])) {
            $data_donor[] = $value['donor'][$donor];
            $parent = new \stdClass();
            $parent->name = $value['name'];
            $parent->id = $colors[$i] . '_' . $value['id'];
            $nodes[] = $parent;
            $node_check[] = $value['name'];
            $i++;

          }
          else {
            $data_donor = [];
          }
        }
        else {
          $data_donor = $value['donor'];
          $parent = new \stdClass();
          $parent->name = $value['name'];
          $parent->id = $colors[$i] . '_' . $value['id'];
          $nodes[] = $parent;
          $node_check[] = $value['name'];
          $i++;
        }

        foreach ($data_donor as $donor_value) {
          if ($j == 5) {
            $j = 0;
          }
          if (!in_array($donor_value['name'], $node_check)) {
            $parent = new \stdClass();
            $parent->name = $donor_value['name'];
            $parent->id = $colors[$j] . '_' . $donor_value['id'];
            $nodes[] = $parent;
            $node_check[] = $donor_value['name'];
            $j++;
          }

          $children = new \stdClass();
          $children->source = array_search($donor_value['name'], $node_check);
          $children->value = $donor_value['amount'];
          $children->target = array_search($value['name'], $node_check);
          $links[] = $children;
        }
      }
      ksort($nodes);
      $countries = new \stdClass();
      $countries->nodes = $nodes;
      $countries->links = $links;
    }
    header('Content-Type: application/json');
    $data = json_encode($countries);
    echo $data;
    die;
  }

}
