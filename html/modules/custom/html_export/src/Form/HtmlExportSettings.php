<?php

namespace Drupal\html_export\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains  Html Export Settings.
 */
class HtmlExportSettings extends ConfigFormBase {


  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates an object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

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
    $types = $this->entityTypeManager->getStorage('node_type')
      ->loadMultiple();
    foreach ($types as $key => $type) {
      $options[$key] = $type->get('name');
    }
    $form['html_export_available_types'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Type based selectors'),
      '#default_value' => $this->config('html_export.settings')->get('html_export_available_types'),
      '#options' => $options,
      '#description' => $this->t('Select the types that are available for publishing as options.'),
    ];
    $form['html_export_other_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Other paths to export'),
      '#description' => $this->t("Provide one path per line of additional paths to export."),
      '#default_value' => $this->config('html_export.settings')->get('html_export_other_paths'),
    ];
    $form['html_export_dom_remove'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Default dom removal'),
      '#description' => $this->t("Supply a css style selector to target the removal of. This is especially useful for removing things like the admin toolbar, tabs, or other elements you don't want to have exported. One selectors per line."),
      '#default_value' => $this->config('html_export.settings')->get('html_export_dom_remove'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
