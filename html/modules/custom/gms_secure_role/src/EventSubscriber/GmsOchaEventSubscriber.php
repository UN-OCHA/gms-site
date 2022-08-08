<?php

namespace Drupal\gms_ocha\EventSubscriber;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\social_auth\Event\UserEvent;
use Drupal\social_auth\Event\SocialAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class GmsOchaEventSubscriber implements EventSubscriberInterface {

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $configFactory;

  /**
   * The logger factory service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * SocialAuthSubscriber constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Used for accessing configuration object factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(MessengerInterface $messenger, ConfigFactory $config_factory, LoggerChannelFactoryInterface $logger_factory) {
    $this->messenger = $messenger;
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      SocialAuthEvents::USER_LOGIN => ['showPopup'],
      HookEventDispatcherInterface::USER_LOGIN => ['showPopup'],
    ];
  }

  /**
   * React to a user being created.
   *
   * @param \Drupal\social_auth\Event\UserEvent $event
   *   The user event object.
   */
  public function showPopup(UserEvent $event) {

  }

}
