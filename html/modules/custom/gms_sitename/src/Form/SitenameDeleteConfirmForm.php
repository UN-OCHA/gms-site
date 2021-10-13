<?php
namespace Drupal\gms_sitename\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfirmFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SitenameDeleteConfirmForm extends ConfirmFormBase{
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sitename_delete_confirm';
  }
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
    return $this->t('Are you sure you want to delete the sitename '.$this->sitename.' ?');
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
    $id = \Drupal::request()->query->get('id');
    $params = \Drupal::request()->request->all();
   // print_r($this->cid); exit();
    $path = \Drupal::request()->getpathInfo();
    $arg  = explode('/',$path);
    //echo '<pre>';
   // print_r($arg); exit();
    $sitename = $arg[6];
    //  $_SESSION['dblog_overview_filter'] = [];
   // $this->connection->truncate('watchdog')->execute();
    $query = \Drupal::database();
    $query->delete('gms_sitename_data')
      ->condition('sitename',$sitename)
      ->execute();
//    drupal_set_message("succesfully deleted");
//    $form_state->setRedirect('mydata.display_table_controller_display');
    $this->messenger()->addStatus($this->t('Sitename '.$sitename.' deleted.'));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
