<?php

namespace Drupal\gms_pdflink\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Mpdf\Mpdf;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Print controller.
 */
class PrintSectionController extends ControllerBase {

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, RequestStack $request, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->request = $request;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('request_stack'),
      $container->get('database'),
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
   */
  public function viewPrint($export_type, $entity_type, $entity_id) {
    if (!empty($entity_id) && is_numeric($entity_id)) {
      $node_id = $entity_id;
      $node_storage = $this->entityTypeManager->getStorage('node');
      $node = $node_storage->load($node_id);
      $node_title = $node->get('title')->value;
      $query = $this->database->select('menu_link_content_data', 'gs');
      $query->fields('gs', ['id']);
      $query->condition('gs.link__uri', 'entity:node/' . $node_id);
      $result = $query->execute()->fetch();
      $plid = $result->id;
      $menu_link_content_id = $plid;
      $menu_content_storage = $this->entityTypeManager->getStorage('menu_link_content');
      $menu_link_content = current($menu_content_storage->loadByProperties(['id' => $menu_link_content_id]));
      $parent_prop = "menu_link_content:{$menu_link_content->uuid()}";
      $menu_link_content_childs = $menu_content_storage->loadByProperties(
        ['parent' => $parent_prop, 'enabled' => 1]
      );
      $content = '';
      $nidArr = [];
      foreach ($menu_link_content_childs as $c) {
        $r = $c->get('link')->getValue()[0];
        if (preg_match('/entity:node/i', $r['uri'])) {
          $new_nid = substr($r['uri'], 12);
          $nidArr[] = $new_nid;
          $entity_type = 'node';
          $view_mode = 'default';
          $node = $this->entityTypeManager->getStorage('node')->load($new_nid);
          $output1 = $this->entityTypeManager->getViewBuilder($entity_type)->view($node, $view_mode);
          $content .= Markup::create($this->renderer->render($output1));
        }
        $this->entityTypeManager->getStorage('node')->resetCache([$node_id]);
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
      // $host = \Drupal::request()->getSchemeAndHttpHost()
      // .  \Drupal::request()->getBasePath();
      $host = $this->request->getCurrentRequest()->getSchemeAndHttpHost();
      $html = str_replace("src=\"/sites/", "src=\"" . $host . "/sites/", $html);
      // $html = str_replace("src=\"/sites/", "src=\"../sites/", $html);
      $html = preg_replace('/>\s+</', "><", $html);
      $fileName = str_replace(" ", "_", strtolower($node_title)) . ".pdf";
      // $filepath = 'sites/default/files/temp_generate_form_pdf/' . $fileName;
      $mpdf = new Mpdf(['format' => 'B4']);
      $mpdf->curlAllowUnsafeSslRequests = TRUE;
      $mpdf->WriteHTML($html);
      $mpdf->AddPage('L');
      $mpdf->Output($fileName, 'D');
      die;
    }
    else {
      global $base_url;
      $this->messenger()->addMessage($this->t('Access denied.'), 'error');
      $response = new RedirectResponse($base_url, 301);
      $response->send();
    }

  }

}
