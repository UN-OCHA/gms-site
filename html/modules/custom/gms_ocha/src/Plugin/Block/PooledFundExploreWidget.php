<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "gms_ocha_pooled_fund_explore_widget",
 *  admin_label = @Translation("GMS Ocha Pooled Fund Explore Widget")
 * )
 */
class PooledFundExploreWidget extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build =    [
      '#theme' => 'pooled_fund_explore_widget',
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }
}
