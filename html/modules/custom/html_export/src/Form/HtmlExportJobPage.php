<?php

namespace Drupal\html_export\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains Html Export Job.
 */
class HtmlExportJobPage extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, FileSystemInterface $file_system) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('file_system')
    );
  }

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
    $path_provders = $this->moduleHandler->invokeAll('html_export_add_path_provider');
    $this->moduleHandler->alter('html_export_add_path_provider', $path_provders);
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
      '#value' => $this->t('Export as html'),
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
    $path_provders = $this->moduleHandler->invokeAll('html_export_add_path_provider');
    $this->moduleHandler->alter('html_export_add_path_provider', $path_provders);
    $paths = [];
    $front_found = FALSE;
    $uid = NULL;
    $menu_parameters = new MenuTreeParameters();
    $menu_parameters->setMinDepth(1);
    $menu_parameters->setMaxDepth(NULL);
    $links = [];
    $storage = $this->entityTypeManager->getStorage('menu_link_content');
    $menu_links = $storage->loadByProperties(['menu_name' => 'menu-ocha']);
    if (empty($menu_links)) {
      return $links;
    }
    foreach ($menu_links as $menu_link) {
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
    $this->fileSystem->prepareDirectory($export_path, FileSystemInterface::CREATE_DIRECTORY);
    $this->fileSystem->prepareDirectory($export_path);
    $batch = [];
    $batch = $this->generateBatch2($anonymous, $export_path, $paths, $export_folder, $dom_remove = [], $uid = NULL, $front_found = FALSE, $render_errors = FALSE, $format = '');
    batch_set($batch);
  }

  /**
   * HTML Export Job Start batch Process.
   */
  public function generateBatch2($anonymous, $export_path, $paths, $export_folder, $dom_remove = [], $uid = NULL, $front_found = TRUE, $render_errors = FALSE, $format = '') {
    $result = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['type' => 'static_page', 'status' => 1]);
    $paths = array_keys($result);
    // $paths = array_slice($paths, 0, 5, true);
    $operations = [];
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
      'finished' => 'html_export_batch_import_finished',
      'title' => $this->t('Processing html export'),
      'init_message' => $this->t('HTML export is starting.'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('HTML export has encountered an error.'),
    ];
    return $batch;
  }

}
