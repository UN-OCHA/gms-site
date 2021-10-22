<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Render\Markup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Banner block.
 *
 * @Block(
 *  id = "gms_ocha_banner",
 *  admin_label = @Translation("GMS Ocha Banner")
 * )
 */
class GmsBanner extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\search\Form\SearchBlockForm');
    $output = \Drupal::service('renderer')->render($form);
    $gmaOverview = Url::fromRoute('entity.node.canonical', ['node' => 200])->toString();
    $tooltip = Url::fromRoute('entity.node.canonical', ['node' => 155])->toString();
    $markup = "<h1>GMS Help portal</h1>
    <p>The GMS Help portal has been designed to assist each user group of the system to be able to navigate through  the GMS system.</p>
    <div class=\"block block-gms-ocha first last odd\" id=\"block-gms-ocha-gms-portal\"></div>
    <p><a href='" . $gmaOverview . "'>" . t('GMS Overview') . "</a>
    <a href='" . $tooltip . "'>" . t('Tool Tips') . "</a> " . $output . "</p>";
    return [
      '#markup' => Markup::create($markup . "\n"),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
