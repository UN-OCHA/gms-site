<?php

namespace Drupal\gms_pdflink\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\node\Entity\Node;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Print controller.
 */
class PrintSectionController extends ControllerBase {

  /**
   * Print an entity to the selected format.
   *
   * @param string $export_type
   *   The export type.
   * @param string $entity_type
   *   The entity type.
   * @param int $entity_id
   *   The entity id.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object on error otherwise the Print is sent.
   */
  public function viewPrint($export_type, $entity_type, $entity_id) {
    if (!empty($entity_id) && is_numeric($entity_id)) {
      $node_id = $entity_id;
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node = $node_storage->load($node_id);
      $node_title = $node->get('title')->value;
      $query = \Drupal::database()->select('menu_link_content_data', 'gs');
      $query->fields('gs', ['id']);
      $query->condition('gs.link__uri', 'entity:node/' . $node_id);
      $result = $query->execute()->fetch();
      $plid = $result->id;
      $menu_link_content_id = $plid;
      $menu_content_storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
      $menu_link_content = current($menu_content_storage->loadByProperties(['id' => $menu_link_content_id]));
      $parent_prop = "menu_link_content:{$menu_link_content->uuid()}";
      $menu_link_content_childs = $menu_content_storage->loadByProperties(
        ['parent' => $parent_prop, 'enabled' => 1]
      );
      $content = '';
      $nidArr = [];
      \Drupal::service('entity.memory_cache');
      foreach ($menu_link_content_childs as $c) {
        $r = $c->get('link')->getValue()[0];
        if (preg_match('/entity:node/i', $r['uri'])) {
          $new_nid = substr($r['uri'], 12);
          $nidArr[] = $new_nid;
          $entity_type = 'node';
          $view_mode = 'default';
          $node = Node::load($new_nid);
          $output1 = \Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($node, $view_mode);
          $content .= Markup::create(\Drupal::service('renderer')->render($output1));
        }
        \Drupal::entityTypeManager()->getStorage('node')->resetCache([$node_id]);
      }
      $html = '<html>
                <head>
                  <title>' . $node_title . '</title>
                  <style>
                  table {
                      max-width: 990px !important;
                  }
                  body {
                      max-width: 990px !important;
                  }
                  img {
                      max-width: 650px !important;
                  }
                  </style>
                </head>
                <body>' . $content . '</body>
             </html>';
      $host = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBasePath();
      $html = str_replace("src=\"/sites/", "src=\"" . $host . "/sites/", $html);
      $fileName = str_replace(" ", "_", strtolower($node_title)) . ".pdf";
      $options = new Options();
      $options->set('isRemoteEnabled', TRUE);
      $options->set('defaultFont', 'Calibri');
      $options->set('isHtml5ParserEnabled', 'TRUE');
      $dompdf = new Dompdf($options);
      $dompdf->load_html($html);
      $dompdf->setPaper('B4', 'landscape');
      $dompdf->render();
      $output = $dompdf->output();
      if (!is_dir('public://temp_generate_form_pdf')) {
        mkdir("public://temp_generate_form_pdf", 0777);
      }
      $filepath = 'sites/default/files/temp_generate_form_pdf/' . $fileName;
      $fp = fopen($filepath, "w+");
      fwrite($fp, $output);
      fclose($fp);
      $headers = [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment;filename="' . $fileName . '"',
      ];
      unset($html);
      unset($options);
      unset($fileName);
      unset($host);
      return new BinaryFileResponse($filepath, 200, $headers, TRUE);
    }
    else {
      global $base_url;
      \Drupal::messenger()->addMessage(t('Access denied.'), 'error');
      $response = new RedirectResponse($base_url, 301);
      $response->send();
    }

  }

}
