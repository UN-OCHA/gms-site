<?php

namespace Drupal\gms_pdflink\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Print controller.
 */
class ViewPdfController extends ControllerBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Retrieves the currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->request = $request;//\Drupal::request();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('request_stack'),
    );
  }

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
      $new_nid = $entity_id;
      $entity_type = 'node';
      $view_mode = 'default';
      $content = '';
      $node = $this->entityTypeManager->getStorage('node')->load($new_nid);
      $node_title = $node->get('title')->value;
      $output1 = $this->entityTypeManager->getViewBuilder($entity_type)->view($node, $view_mode);
      $content .= Markup::create($this->renderer->render($output1));
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
      // $host = \Drupal::request()->getSchemeAndHttpHost() .  \Drupal::request()->getBasePath();
      $host = $this->request->getCurrentRequest()->getSchemeAndHttpHost();
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
        'Content-Type'     => 'application/pdf',
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
      $this->messenger()->addMessage($this->t('Access denied.'), 'error');
      $response = new RedirectResponse($base_url, 301);
      $response->send();
    }
  }

}
