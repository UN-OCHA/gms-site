<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;

/**
 * Provides a WelcomeText block.
 *
 * @Block(
 *   id = "welcome_text",
 *   admin_label = @Translation("Welcome text")
 * )
 */
class WelcomeText extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    $user = User::load(\Drupal::currentUser()->id());
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
