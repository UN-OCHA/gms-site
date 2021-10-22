<?php

namespace Drupal\html_export\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Contains Html Export Job.
 */
class HtmlExportJobPage extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return '_html_export_job_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    $path_provders = \Drupal::moduleHandler()->invokeAll('html_export_add_path_provider');
    \Drupal::moduleHandler()->alter('html_export_add_path_provider', $path_provders);
    foreach ($path_provders as $key => $provider) {
      $options[$key] = $provider['title'];
    }
    $destination = 'sites/default/files/html_export/html_export.zip';
    if (file_exists($destination)) {
      $form['download_ink'] = [
        '#type' => 'markup',
        '#markup' => Link::fromTextAndUrl('Click here to download html', Url::fromRoute('html_export.download_folder'))->toString(),
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Export as html'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $destination = 'sites/default/files/html_export/html_export.zip';
    if (file_exists($destination)) {
      unlink($destination);
    }
    $path_provders = \Drupal::moduleHandler()->invokeAll('html_export_add_path_provider');
    \Drupal::moduleHandler()->alter('html_export_add_path_provider', $path_provders);
    $paths = [];
    $front_found = FALSE;
    $uid = NULL;
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMinDepth(1);
    $menu_parameters->setMaxDepth(NULL);
    $links = [];
    $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
    $menu_links = $storage->loadByProperties(['menu_name' => 'menu-ocha']);
    if (empty($menu_links)) {
      return $links;
    }
    foreach ($menu_links as $mlid => $menu_link) {
      $link = [];
      if (isset($link['uri']) && substr($link['uri'], 0, 11) === 'entity:node') {
        $paths[] = substr($menu_link->link->uri, 7);
      }
      else {
        $paths[] = $menu_link->link->uri;
      }
      $link['uri'] = $menu_link->link->uri;
      $links[] = $link;
    }
    $dom_remove = [];
    $format = 'zip_download';
    $render_errors = TRUE;
    $uid = NULL;
    $this->htmlExportProcessJob($paths, 'export', $dom_remove, $uid, $front_found, $render_errors, $format);
  }

  /**
   * HTML Export Job Start.
   */
  public function htmlExportProcessJob($paths, $export_folder, $dom_remove = [], $uid = NULL, $front_found = FALSE, $render_errors = FALSE, $format = '') {
    $GLOBALS['html_export_active'] = TRUE;
    $anonymous = FALSE;
    if ($uid != NULL) {
    }
    elseif ($uid == 0) {
      $anonymous = TRUE;
    }
    $dir = 'sites/default/files/html_export';
    $export_path = $dir . '/' . $export_folder;
    \Drupal::service('file_system')->prepareDirectory($export_path, FileSystemInterface::CREATE_DIRECTORY);
    \Drupal::service('file_system')->prepareDirectory($export_path);
    $batch = [];
    $batch = $this->generateBatch2($anonymous, $export_path, $paths, $export_folder, $dom_remove = [], $uid = NULL, $front_found = FALSE, $render_errors = FALSE, $format = '');
    batch_set($batch);
  }

  /**
   * HTML Export Job Start batch Process.
   */
  public function generateBatch2($anonymous, $export_path, $paths, $export_folder, $dom_remove = [], $uid = NULL, $front_found = TRUE, $render_errors = FALSE, $format = '') {
    $result = \Drupal::entityTypeManager()->getStorage('node')
      ->loadByProperties(['type' => 'static_page', 'status' => 1]);
    $paths = array_keys($result);
    // $paths = array_slice($paths, 0, 5, true);
    $operations = [];
    $i = 0;
    $operations[] = ['html_export_render_pages',
      [$paths, $export_path, $dom_remove, $anonymous, $front_found, $render_errors,
      ],
    ];
    $operations[] = ['html_export_home_pages',
      [$export_path, $dom_remove, $anonymous, $render_errors,
      ],
    ];
    $batch = [
      'operations' => $operations,
      'finished' => 'batch_import_finished',
      'title' => $this->t('Processing html export'),
      'init_message' => $this->t('HTML export is starting.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('HTML export has encountered an error.'),
    ];
    return $batch;
  }

}
