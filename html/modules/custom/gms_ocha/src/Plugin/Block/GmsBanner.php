<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Banner block.
 *
 * @Block(
 *  id = "gms_ocha_banner",
 *  admin_label = @Translation("GMS Ocha Banner")
 * )
 */
class GmsBanner extends BlockBase implements ContainerFactoryPluginInterface {

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
    $form = $this->formBuilder->getForm('Drupal\search\Form\SearchBlockForm');
    $output = $this->renderer->render($form);
    $gmaOverview = Url::fromRoute('entity.node.canonical', ['node' => 200])->toString();
    $tooltip = Url::fromRoute('entity.node.canonical', ['node' => 155])->toString();
    $markup = "<h1>GMS Help portal</h1>
    <p>The GMS Help portal has been designed to assist each user group of the system to be able to navigate through  the GMS system.</p>
    <div class=\"block block-gms-ocha first last odd\" id=\"block-gms-ocha-gms-portal\"></div>
    <p><a href='" . $gmaOverview . "'>" . $this->t('GMS Overview') . "</a>
    <a href='" . $tooltip . "'>" . $this->t('Tool Tips') . "</a> " . $output . "</p>";
    return [
      '#markup' => Markup::create($markup . "\n"),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
