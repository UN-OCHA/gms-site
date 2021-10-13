<?php

namespace Drupal\gms_html_export\Form;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\PublicStream;

/**
 * Form with examples on how to use cache.
 */
class BatchExportForm extends FormBase {
  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;
  public function __construct(FileSystemInterface $file_system) {
    $this->fileSystem = $file_system;
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['export_node'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Export Node'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path_providers = array();
    $query = \Drupal::entityQuery('node'); //content type
    $nids = $query->execute();
    //  echo '<pre>';
    // print_r($nids); exit();
    foreach ($nids as $nid) {
      $paths[] = 'node/' . $nid;
    }
    //  print_r($paths); exit();
    $export_folder = 'export';
    $anonymous = FALSE;
    $other_paths = '<front>';
    $render_errors = FALSE;
    $front_found = FALSE;
    $dom_remove = ['admin-menu', 'devel', 'block-print-ui-print-links', 'block-print-ui'];
    $uid = 0;
    $format = 'zip_download';
    $dir = PublicStream::basePath();
    //print $dir; exit(); //sites/default/files
    $this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY);
    $export_path = $dir . '/' . $export_folder;
   // print $export_path; exit();
    $batch = array(
      'title' => t('Exporting Node...'),
      'operations' => array(
        array(
          '\Drupal\gms_html_export\ExportNode::html_export_render_pages',
          array( $paths,
            $export_path,
            $dom_remove,
            $anonymous,
            $front_found,
            $render_errors,)
        ),
        array(
          '\Drupal\gms_html_export\ExportNode::html_export_copy_all_resources',
          array($paths)
        ),
        array(
          '\Drupal\gms_html_export\ExportNode::html_export_cleanup',
          array($paths)
        ),
      ),
      'finished' => '\Drupal\gms_html_export\ExportNode::exportNodeExampleFinishedCallback',
    );

    batch_set($batch);
  }

}
