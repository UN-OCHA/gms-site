<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Sanket Chart block.
 *
 * @Block(
 *  id = "gms_ocha_sankey_chart",
 *  admin_label = @Translation("GMS Ocha Sankey Chart")
 * )
 */
class SankeyChart extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\gms_ocha\Form\SanKeyForm');
    $output = \Drupal::service('renderer')->render($form);
    $build = [
      '#theme' => 'pooled_fund_sankey_chart',
      '#form_var' => $form,
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }

}
