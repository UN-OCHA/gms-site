<?php
namespace Drupal\gms_sitename\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

class SiteNameForm extends FormBase{

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gms_sitename_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $sitename=NULL) {
//    $get_sitename = '';
//    $get_ctype = '';
//    $get_condition = '';

    if($sitename){
    //  echo '<pre>';
      // print $sitename; exit();
      $query = \Drupal::database()->select('gms_sitename_data', 'gs');
      $query->fields('gs', array('id','check_type','condition_type','sitename'));
      $query->condition('gs.sitename', $sitename);
      $result = $query->execute()->fetchAll();
      // print_r($result); exit();
      $get_id = $result[0]->id;
      $get_sitename = $result[0]->sitename;
      $get_ctype = $result[0]->check_type;
      $get_condition = $result[0]->condition_type;
    }

    $options = array('ctype' => 'Content Type', 'url' => 'URL');
    $form['gms_sitename'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('GMS Sitename Settings'),
    ];
    $form['description'] = [
      '#markup' => '<p>' . $this->t('Submitting this form will initiate site name.').'</p>'
    ];

    $form['gms_sitename']['check_types'] = [
      '#type' => 'select',
      '#title' => $this->t('Types'),
      '#options' => $options,
      '#default_value' => isset($sitename) ? $this->t($get_ctype) : $this->t(''),
      '#empty_option' => $this->t('-select-'),
      '#description' => $this->t('Select check types'),
    ];

    $form['gms_sitename']['condition_types'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Condition'),
      '#default_value' => isset($sitename) ? $this->t($get_condition) : $this->t(''),
      '#description' => $this->t(''),
    ];

    $form['gms_sitename']['sitename'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sitename'),
      '#default_value' => isset($sitename) ? $this->t($get_sitename) : $this->t(''),
      '#description' => $this->t(''),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['gms_sitename']['site_id'] = array(
      '#type' => 'hidden',
      '#value' => isset($sitename) ? $get_id : $this->t(''),
    );

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => isset($sitename) ? $this->t('Update') : $this->t('Submit'),
    ];

    $query = \Drupal::database()->select('gms_sitename_data', 'gs');
    $query->fields('gs', ['id','check_type','condition_type','sitename']);
    //For the pagination we need to extend the pagerselectextender and
    //limit in the query
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $results = $pager->execute()->fetchAll();
    $output = array();
    $header = [
      'ID'=>t('ID'),
      'Type'=>t('Type'),
      'Condition'=>t('Condition'),
      'Site Name'=>t('Site Name'),
      'Operation'=>t('Operation'),
      ''=>t('')

    ];

    foreach ($results as $result) {
    //  $delete = Url::fromUserInput('/admin/config/system/gms_sitename/confirm/'.$result->id);
      $edit_link = Link::createFromRoute($this->t('Edit'), 'gms_sitename.edit', ['sitename'=>$result->sitename], [ 'absolute' => TRUE]);
      $delete_link = Link::createFromRoute($this->t('Delete'),'gms_sitename.confirm',['sitename'=>$result->sitename], ['absolute' => TRUE]);

        $output[$result->id] = [
          'ID' => $result->id,
          'Type' => $result->check_type,
          'Condition' => $result->condition_type,
          'Site Name' => $result->sitename,
          'Operation'=> $edit_link->toString() ,
          ''=>$delete_link->toString()
        ];
    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#weight'=> 100,
      '#empty' => t('No records found'),
    ];
    $form['pager'] = array(
      '#type' => 'pager'
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $check_type = $form_state->getValue('check_types');
    $condition_type = $form_state->getValue('condition_types');
    $sitename = $form_state->getValue('sitename');
    if (empty($check_type)) {
      $form_state->setErrorByName('check_type', $this->t('Please set type.'));
    }
    if (empty($condition_type)) {
      $form_state->setErrorByName('check_type', $this->t('Please set Condition.'));
    }
    if (empty($sitename)) {
      $form_state->setErrorByName('check_type', $this->t('Please Set Sitename.'));
    }else{
      $query = \Drupal::database()->select('gms_sitename_data', 'gs');
      $query->fields('gs', array('sitename'));
      $query->condition('gs.sitename', $sitename);
      $query->condition('gs.condition_type', $condition_type);
      $query->condition('gs.check_type', $check_type);
      $result = $query->execute()->fetchField();
     // print_r($result); exit();
      if ($result) {
        $form_state->setErrorByName('sitename', 'Sitename with this condition already exists.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  //  print \Drupal::request()->request->get('sitename'); exit();
   // print_r($form_state->getValue('id'));exit();
    $site_id = $form_state->getValue('site_id');
//    if ($site_id == '') {
//      echo "Variable is empty.<br>";
//    }
//    if (isset($site_id)) {
//      echo "Variable is set.";
//    }
//    echo empty($site_id); exit();
    $get_sitename = \Drupal::request()->request->get('sitename');
   // echo $get_sitename .'--'.$site_id; exit();
    $check_type = $form_state->getValue('check_types');
    $condition_type = $form_state->getValue('condition_types');
    $sitename = $form_state->getValue('sitename');
    $data = array(
      'check_type' => $check_type,
      'condition_type' => $condition_type,
       'sitename' => $sitename
    );
   // print_r($check_type);exit();
    if($site_id != ''){
      $query = \Drupal::database();
      $query->update('gms_sitename_data')
        ->fields($data)
        ->condition('id', $site_id)
        ->execute();
      if($query){
        \Drupal::messenger()->addMessage('Sitename record updated successfully.');
      }
    }else {
      $query = \Drupal::database();
      $query->insert('gms_sitename_data')
        ->fields($data)
        ->execute();
      if ($query) {
        \Drupal::messenger()->addMessage('Sitename record added successfully.');
      }
    }
  }

  /**
   * Load the site name data.
   */
  function sitename_load($sitename_id) {
    $query = \Drupal::database()->select('gms_sitename_data', 'gs');
    $query->fields('gs', array());
    $query->condition('gs.id', $sitename_id);
    $result = $query->execute()->fetchAll();
    if ($result) {
      return $result[0];
    }
  }
}





