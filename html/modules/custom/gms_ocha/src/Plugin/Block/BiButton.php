<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Banner block.
 *
 * @Block(
 *  id = "gms_ocha_bibutton",
 *  admin_label = @Translation("GMS Ocha BiButton")
 * )
 */
class BiButton extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The FormBuilder object.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Entity Manager call.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
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
      $container->get('renderer'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $markup = "<a class=\"cbpf-public-bi\" target='_blank' href=\"/bi\" title=\"Public BI\">Public BI</a>";
    return [
      '#markup' => Markup::create($markup . "\n"),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
