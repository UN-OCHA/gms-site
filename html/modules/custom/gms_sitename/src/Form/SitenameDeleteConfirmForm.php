<?php

namespace Drupal\gms_sitename\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * SiteName Delete form.
 */
class SitenameDeleteConfirmForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitename_delete_confirm';
  }

  /**
   * The Site name.
   *
   * @var string
   */
  public $sitename;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $sitename = NULL) {

    $this->sitename = $sitename;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t("Are you sure you want to delete the sitename %name.", ["%name" => $this->sitename]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('gms_sitename.add');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = \Drupal::request()->getpathInfo();
    $arg = explode('/', $path);
    $sitename = $arg[6];
    $query = \Drupal::database();
    $query->delete('gms_sitename_data')
      ->condition('sitename', $sitename)
      ->execute();
    $this->messenger()->addStatus($this->t("Sitename %name. deleted", ["%name" => $sitename]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
