<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * @Block(
 *  id = "gms_ocha_pooled_fund_widget",
 *  admin_label = @Translation("GMS Ocha Pooled Fund Widget")
 * )
 */
class PooledFundWidget extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\gms_ocha\Form\PooledFundWidgetForm');
    $value = $form['#donation'];
    $build =    [
      '#theme' => 'pooled_fund_widget',
      '#pooled_fund_form' => $form,
//      '#donation' => $value,
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }
}
