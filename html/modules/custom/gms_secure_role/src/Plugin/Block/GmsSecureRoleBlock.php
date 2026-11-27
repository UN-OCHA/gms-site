<?php

namespace Drupal\gms_secure_role\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Banner block.
 *
 * @Block(
 *  id = "gms_secure_role",
 *  admin_label = @Translation("GMS Secure Role request")
 * )
 */
class GmsSecureRoleBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Creates a GmsSecureRoleBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Request $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->request = $request;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A configuration array containing information about the plugin
   *     instance.
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
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $markup = '<a href="/gms_secure_role/show_popup" id="show_popup" style="display:none" class="use-ajax ajax-login" >Click</a>';
    $attached = [];

    $show_pop_up = $this->request->getSession()->get('show_pop_up');
    if ($show_pop_up == TRUE) {
      $attached = [
        'library' => [
          'gms_secure_role/gms_secure_role_js',
        ],
        'drupalSettings' => [
          'siteBaseUrl' => $base_url,
        ],
      ];
      $this->request->getSession()->remove('show_pop_up');
    }

    return [
      '#markup' => Markup::create($markup . "\n"),
      '#attached' => $attached,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
