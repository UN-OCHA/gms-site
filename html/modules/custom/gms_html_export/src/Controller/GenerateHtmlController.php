<?php

namespace Drupal\gms_html_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Url;
use Drupal\entity_print\Plugin\ExportTypeManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * GenerateHtml controller.
 */
class GenerateHtmlController extends ControllerBase
{

  public function viewHtml($type)
  {
    $path_providers = array();
    $query = \Drupal::entityQuery('node'); //content type
    $nids = $query->execute();
  //  echo '<pre>';
    // print_r($nids); exit();
    foreach ($nids as $nid) {
      $paths[] = 'node/' . $nid;
    }
    //  print_r($paths); exit();
    $other_paths = '<front>';
    $render_errors = FALSE;
    $front_found = FALSE;
    $dom_remove = ['admin-menu', 'devel', 'block-print-ui-print-links', 'block-print-ui'];
    $uid = 0;
    $format = 'zip_download';
    $this->html_export_process_job($paths, 'export' . \Drupal::time()->getRequestTime(), $dom_remove, $uid, $front_found, $render_errors, $format);

    if (is_file('sites/default/files/html_export/html_export.zip')) {
      Url::fromRoute('sites/default/files/html_export/html_export.zip' ,['attributes' => ['target' => '_blank'],'absolute' => TRUE]);
    }else{

    }
    return [];
  }

  public function html_export_process_job($paths, $export_folder, $dom_remove = array(), $uid = NULL, $front_found = FALSE, $render_errors = FALSE, $format = '')
  {
    $dir = PublicStream::basePath();
//    print_r($dir);
//    exit();
  }

  public function gms_ocha_zip($source, $destination, $include_dir = TRUE)
  {
    if (file_exists($destination)) {
      unlink($destination);
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
      return FALSE;
    }
    $source = str_replace('\\', '/', realpath($source));
    if (is_dir($source) == TRUE) {

      $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

      if ($include_dir) {

        $arr = explode("/", $source);
        $maindir = $arr[count($arr) - 1];

        $source = "";
        for ($i = 0; $i < count($arr) - 1; $i++) {
          $source .= '/' . $arr[$i];
        }

        $source = substr($source, 1);

        $zip->addEmptyDir($maindir);

      }

      foreach ($files as $file) {
        $file = str_replace('\\', '/', $file);

        // Ignore "." and ".." folders.
        if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
          continue;
        }

        $file = realpath($file);

        if (is_dir($file) === TRUE) {
          $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
        } else {
          if (is_file($file) === TRUE) {
            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
          }
        }
      }

    } else {
      if (is_file($source) === TRUE) {
        $zip->addFromString(basename($source), file_get_contents($source));
      }
    }

    return $zip->close();
  }

  public function create_dir_rec($path) {
    $files = glob($path . '/*');
    $dir = DRUPAL_ROOT . '/sites/default/files/html_export/export/' . $path . '/*.*';
    if (!is_dir(dirname($dir))) {
      mkdir(dirname($dir), 0777, TRUE);
    }
    foreach ($files as $file) {
      if (is_dir($file)) {
        $dir = DRUPAL_ROOT . '/sites/default/files/html_export/export/' . $file . '/*.*';
        if (!is_dir(dirname($dir))) {
          mkdir(dirname($dir), 0777, TRUE);
        }
        $this->create_file_rec($file);
        $this->create_dir_rec('sites/default/files/html_export/export/' . $file);
      }
    }
    $this->create_file_rec($path);
  }

  /**
   * No idea.
   *
   * @todo Copies files to a hard-coded file path on gluster.
   */
  public function create_file_rec($source) {
    $files = glob($source . '/*.*');
    foreach ($files as $file) {
      $file_to_go = DRUPAL_ROOT . '/sites/default/files/html_export/export/' . $file;
      copy($file, $file_to_go);
    }
  }



}
