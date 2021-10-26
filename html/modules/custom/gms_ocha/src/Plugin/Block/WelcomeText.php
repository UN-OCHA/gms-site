<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a WelcomeText block.
 *
 * @Block(
 *   id = "welcome_text",
 *   admin_label = @Translation("Welcome text")
 * )
 */
class WelcomeText extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Entity Manager call.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $currentUser, EntityTypeManager $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;

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
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $logged_in = $this->currentUser->isAuthenticated();
    $user = $this->entityTypeManager->getStorage('user')->load($this->currentUser->id());
    $name = $user->get('name')->value;
    $welcome = $logged_in ? "Welcome," . $name : '';
    $logoutUrl = $logged_in ? '<a href="/user/logout">Logout</a>' : '';
    $build = [
      '#theme' => 'gms_ocha_welcome',
      '#welcome' => $welcome,
      '#logoutUrl' => $logoutUrl,
      '#logged_in' => $logged_in,
      '#name' => $name,
      '#attached' => [
        'library' => [
          'gms_ocha/welcome_text',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }

}
