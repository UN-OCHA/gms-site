<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fund  Widget Block.
 *
 * @Block(
 *  id = "gms_ocha_pooled_fund_widget",
 *  admin_label = @Translation("GMS Ocha Pooled Fund Widget")
 * )
 */
class PooledFundWidget extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The FormBuilder object.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Entity Manager call.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A configuration array containing information about the plugin instance.
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin Id.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = $this->formBuilder->getForm('Drupal\gms_ocha\Form\PooledFundWidgetForm');
    $build = [
      '#theme' => 'pooled_fund_widget',
      '#pooled_fund_form' => $form,
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }

}
