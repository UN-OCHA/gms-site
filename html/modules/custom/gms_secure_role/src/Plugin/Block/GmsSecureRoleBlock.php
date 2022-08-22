<?php

namespace Drupal\gms_secure_role\Plugin\Block;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Entity Manager call.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $markup = '<a href="/gms_secure_role/show_popup" id="show_popup" style="display:none" class="use-ajax ajax-login" >Click</a>';
    $attached = [];
    if (isset($_SESSION['show_pop_up']) && $_SESSION['show_pop_up'] == TRUE) {
    $attached = [
      'library' => [
        'gms_secure_role/gms_secure_role_js',
      ],
      'drupalSettings' => [
        'siteBaseUrl' => $base_url,
      ],
    ];
      unset($_SESSION['show_pop_up']);
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
