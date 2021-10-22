<?php

namespace Drupal\html_export\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * @file
 * Contains \Drupal\html_export\Controller\DefaultController.
 */

/**
 * Default controller for the html_export module.
 */
class DefaultController extends ControllerBase {

  /**
   * Download zip file.
   */
  public function htmlExportDownloadFolder() {
    $destination = 'sites/default/files/html_export/html_export.zip';
    if (is_file($destination)) {
      header('Content-type: application/zip');
      header('Content-Disposition: attachment; filename="' . basename($destination) . '"');
      header("Content-length: " . filesize($destination));
      header("Pragma: no-cache");
      header("Expires: 0");
      ob_clean();
      flush();
      readfile($destination);
      exit();
    }
  }

}
