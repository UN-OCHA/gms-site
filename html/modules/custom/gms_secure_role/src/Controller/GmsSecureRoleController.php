<?php

namespace Drupal\gms_secure_role\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for popup_after_login routes.
 */
class GmsSecureRoleController extends ControllerBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder service.
   *
   * @var  \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a MyModuleController object.
   *
   * @param array $configuration
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $formBuilder
   *   The path alias manager.
   */

  public function __construct(
    EntityFormBuilderInterface $formBuilder,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->formBuilder = $formBuilder;
    $this->entityTypeManager = $entityTypeManager;
  }


  /**
   * {@inheritdoc}
   */

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Custom login Modal function.
   */
  public function loginModal() {

    $response = new AjaxResponse();
    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => '700',
      'title' => $this->t('Request for Secure role.'),
    ];

    $message = $this->entityTypeManager
      ->getStorage('contact_message')
      ->create([
        'contact_form' => 'request_form',
      ]);

    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder
      ->getForm($message);
    //dump($message); die;
    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('Login', $modal_form, $options));
    return $response;
  }

}
