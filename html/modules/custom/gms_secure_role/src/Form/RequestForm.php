<?php

namespace Drupal\gms_secure_role\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Request form.
 */
class RequestForm extends FormBase {

  /**
   * Creates a ContentHasher object.
   *
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gms_requestform_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {




    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $site_id = $form_state->getValue('site_id');

  }

}
