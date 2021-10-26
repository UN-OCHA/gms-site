<?php

namespace Drupal\gms_sitename\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SiteName form.
 */
class SiteNameForm extends FormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Creates a ContentHasher object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gms_sitename_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $sitename = NULL) {
    if ($sitename) {
      $query = $this->database->select('gms_sitename_data', 'gs');
      $query->fields('gs', ['id', 'check_type', 'condition_type', 'sitename']);
      $query->condition('gs.sitename', $sitename);
      $result = $query->execute()->fetchAll();
      $get_id = $result[0]->id;
      $get_sitename = $result[0]->sitename;
      $get_ctype = $result[0]->check_type;
      $get_condition = $result[0]->condition_type;
    }
    $options = ['ctype' => 'Content Type', 'url' => 'URL'];
    $form['gms_sitename'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('GMS Sitename Settings'),
    ];
    $form['description'] = [
      '#markup' => '<p>' . $this->t('Submitting this form will initiate site name.') . '</p>',
    ];

    $form['gms_sitename']['check_types'] = [
      '#type' => 'select',
      '#title' => $this->t('Types'),
      '#options' => $options,
      '#default_value' => isset($sitename) ? $get_ctype : '',
      '#empty_option' => $this->t('-select-'),
      '#description' => $this->t('Select check types'),
    ];

    $form['gms_sitename']['condition_types'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Condition'),
      '#default_value' => isset($sitename) ? $get_condition : '',
      '#description' => '',
    ];

    $form['gms_sitename']['sitename'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sitename'),
      '#default_value' => isset($sitename) ? $get_sitename : '',
      '#description' => '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['gms_sitename']['site_id'] = [
      '#type' => 'hidden',
      '#value' => isset($sitename) ? $get_id : '',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => isset($sitename) ? $this->t('Update') : $this->t('Submit'),
    ];

    $query = $this->database->select('gms_sitename_data', 'gs');
    $query->fields('gs', ['id', 'check_type', 'condition_type', 'sitename']);
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $results = $pager->execute()->fetchAll();
    $output = [];
    $header = [
      'ID' => $this->t('ID'),
      'Type' => $this->t('Type'),
      'Condition' => $this->t('Condition'),
      'Site Name' => $this->t('Site Name'),
      'Operation' => $this->t('Operation'),
      '' => '',
    ];

    foreach ($results as $result) {
      $edit_link = Link::createFromRoute($this->t('Edit'), 'gms_sitename.edit', ['sitename' => $result->sitename], ['absolute' => TRUE]);
      $delete_link = Link::createFromRoute($this->t('Delete'), 'gms_sitename.confirm', ['sitename' => $result->sitename], ['absolute' => TRUE]);
      $output[$result->id] = [
        'ID' => $result->id,
        'Type' => $result->check_type,
        'Condition' => $result->condition_type,
        'Site Name' => $result->sitename,
        'Operation' => $edit_link->toString(),
        '' => $delete_link->toString(),
      ];
    }

    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $output,
      '#weight' => 100,
      '#empty' => $this->t('No records found'),
    ];
    $form['pager'] = [
      '#type' => 'pager',
    ];
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
    }
    else {
      $query = $this->database->select('gms_sitename_data', 'gs');
      $query->fields('gs', ['sitename']);
      $query->condition('gs.sitename', $sitename);
      $query->condition('gs.condition_type', $condition_type);
      $query->condition('gs.check_type', $check_type);
      $result = $query->execute()->fetchField();
      if ($result) {
        $form_state->setErrorByName('sitename', 'Sitename with this condition already exists.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $site_id = $form_state->getValue('site_id');
    $check_type = $form_state->getValue('check_types');
    $condition_type = $form_state->getValue('condition_types');
    $sitename = $form_state->getValue('sitename');
    $data = [
      'check_type' => $check_type,
      'condition_type' => $condition_type,
      'sitename' => $sitename,
    ];
    if ($site_id != '') {
      $query = $this->database;
      $query->update('gms_sitename_data')
        ->fields($data)
        ->condition('id', $site_id)
        ->execute();
      if ($query) {
        $this->messenger()->addMessage('Sitename record updated successfully.');
      }
    }
    else {
      $query = $this->database;
      $query->insert('gms_sitename_data')
        ->fields($data)
        ->execute();
      if ($query) {
        $this->messenger()->addMessage('Sitename record added successfully.');
      }
    }
  }

  /**
   * Load the site name data.
   */
  public function sitenameLoad($sitename_id) {
    $query = $this->database->select('gms_sitename_data', 'gs');
    $query->fields('gs', []);
    $query->condition('gs.id', $sitename_id);
    $result = $query->execute()->fetchAll();
    if ($result) {
      return $result[0];
    }

  }

}
