<?php

namespace Drupal\gms_group_migrate\Plugin\migrate\source;

use Drupal\node\Plugin\migrate\source\d7\Node;
use Drupal\migrate\Row;

/**
 * Extends the D7 Node source plugin so we can grab OG info.
 *
 * @MigrateSource(
 *   id = "d7_node_custom",
 *   source_module = "node"
 * )
 */
class CustomNode extends Node {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Grab our nid and grab the Group ID from the D7 OG table.
    $nid = $row->getSourceProperty('nid');
    $type = $row->getSourceProperty('type');
    $row->setSourceProperty('domain_access_node', $this->getDomainTargetIds($nid));
    return parent::prepareRow($row);
  }

  /**
   * Helper method to get the gids as target ids from d7 domain_access.
   *
   * @param int $nid
   *   Nid of the current row.
   *
   * @return array
   *   returns target ids of domains
   */
  private function getDomainTargetIds(int $nid) {
    $row_source_properties = [];

    $domains = $this->select('domain_access', 'da')
      ->fields('da', ['gid'])
      ->condition('da.realm', 'domain_id')
      ->condition('da.nid', $nid)
      ->execute()
      ->fetchCol();

    foreach ($domains as $domain) {
      $domain_target_ids = $this->select('domain', 'da')
        ->fields('da', ['machine_name'])
        ->condition('da.domain_id', $domain)
        ->execute()
        ->fetchCol();
      $row_source_properties[] = ['target_id' => $domain_target_ids[0]];
    }
    return $row_source_properties;
  }

}
