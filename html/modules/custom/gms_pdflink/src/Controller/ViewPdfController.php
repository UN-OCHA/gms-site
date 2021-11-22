<?php

namespace Drupal\gms_pdflink\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->request = $request;
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
      $html = 'TEST DATA';
      $params = [
        'debug' => (getenv("PHP_ENVIRONMENT") == "development") ? TRUE : FALSE,
        'media' => 'print',
        'output' => 'pdf',
        'service' => 'gms',
        'pdfLandscape' => 'true',
      ];
      ocha_snap($html, $params);
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
