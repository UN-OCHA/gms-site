<?php

namespace Drupal\html_export\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Contains  Html Export Settings.
 */
class HtmlExportSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_html_export_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('html_export.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['html_export.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $types = \Drupal::entityTypeManager()
      ->getStorage('node_type')
      ->loadMultiple();
    foreach ($types as $key => $type) {
      $options[$key] = $type->get('name');
    }
    $form['html_export_available_types'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => t('Type based selectors'),
      '#default_value' => \Drupal::config('html_export.settings')->get('html_export_available_types'),
      '#options' => $options,
      '#description' => t('Select the types that are available for publishing as options.'),
    ];
    $form['html_export_other_paths'] = [
      '#type' => 'textarea',
      '#title' => t('Other paths to export'),
      '#description' => t("Provide one path per line of additional paths to export."),
      '#default_value' => \Drupal::config('html_export.settings')->get('html_export_other_paths'),
    ];
    $form['html_export_dom_remove'] = [
      '#type' => 'textarea',
      '#title' => t('Default dom removal'),
      '#description' => t("Supply a css style selector to target the removal of. This is especially useful for removing things like the admin toolbar, tabs, or other elements you don't want to have exported. One selectors per line."),
      '#default_value' => \Drupal::config('html_export.settings')->get('html_export_dom_remove'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
