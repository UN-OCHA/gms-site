<?php
/**
 * @file
 * Contains \Drupal\dictionary\Form\TermForm.
 */

namespace Drupal\gms_secure_role\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the content_entity_example entity edit forms.
 *
 * @ingroup content_entity_example
 */
class SecureRoleForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\gms_secure_role\Entity\SecureRole */
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Redirect to term list after save.
    $form_state->setRedirect('<front>');
    $entity = $this->getEntity();
    $entity->save();
  }

}
