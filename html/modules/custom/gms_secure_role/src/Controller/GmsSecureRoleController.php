<?php

namespace Drupal\gms_secure_role\Controller;

use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;

/**
 * Controller routines for popup_after_login routes.
 */
class GmsSecureRoleController extends ControllerBase {

  /**
   * Custom login Modal function.
   */
  public function login_modal() {

      $response = new AjaxResponse();
      $options = [
        'dialogClass' => 'popup-dialog-class',
        'width' => '700',
        'title' => $this->t('Request for Secure role.'),
      ];

      $message = \Drupal::entityTypeManager()
        ->getStorage('contact_message')
        ->create(array(
          'contact_form' => 'request_form',
        ));

      // Get the modal form using the form builder.
      $modal_form = \Drupal::service('entity.form_builder')->getForm($message);
      // Add an AJAX command to open a modal dialog with the form as the content.
      $response->addCommand(new OpenModalDialogCommand('Login', $modal_form, $options));
      return $response;
    }

}

