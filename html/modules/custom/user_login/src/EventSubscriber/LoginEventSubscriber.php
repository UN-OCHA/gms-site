<?php

namespace Drupal\user_login\EventSubscriber;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber AllFormsEventSubscriber.
 */
class LoginEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::FORM_ALTER => 'formModification',
    ];
  }

  /**
   * Implementing hook_form_alter.
   */
  public function formModification($event) {
  }

}
