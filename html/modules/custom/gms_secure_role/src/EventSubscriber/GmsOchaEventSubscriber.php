<?php

namespace Drupal\gms_secure_role\EventSubscriber;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\social_auth\Event\SocialAuthEvents;
use Drupal\social_auth\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class GmsOchaEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      SocialAuthEvents::USER_LOGIN => ['showPopup'],
      HookEventDispatcherInterface::USER_LOGIN => 'showNormalPopup',
    ];
  }

  /**
   * React to a user being created.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The user event object.
   */
  public function showPopup(UserEvent $event) {
    $account = $event->getUser();
    if (in_array('non_verified', $account->getRoles())) {
      $_SESSION['show_pop_up'] = TRUE;
    }
  }

  /**
   * React to a user being created.The user event object.
   */
  public function showNormalPopup($event) {
    $account = $event->getAccount();
    if (in_array('non_verified', $account->getRoles())) {
      $_SESSION['show_pop_up'] = TRUE;
    }
  }

}
