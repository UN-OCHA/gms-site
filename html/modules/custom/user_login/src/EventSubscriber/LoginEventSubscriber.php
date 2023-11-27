<?php

namespace Drupal\user_login\EventSubscriber;

// Use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;.
use Drupal\core_event_dispatcher\FormHookEvents;
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
      FormHookEvents::FORM_ALTER => 'formModification',
    ];
  }

  /**
   * Implementing hook_form_alter.
   */
  public function formModification($event) {
    if ($event->getFormId() == 'user_login_form') {
      $form = &$event->getForm();
      $form['#access'] = FALSE;
    }
  }

}
