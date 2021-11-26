<?php

namespace Drupal\gms_pdflink\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
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
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, RequestStack $request, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->request = $request;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('request_stack'),
      $container->get('config.factory'),
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
      $entity_type = 'node';

      $node       = $this->entityTypeManager->getStorage('node')->load($entity_id);
      $node_title = $node->get('title')->value;
      $filename   = Html::cleanCssIdentifier($node_title) . '.pdf';

      // Optionally check if $filename exists in a local cache directory.
      // If so, we could serve that file instead of generating it again.
      // This would of course require us to save the generated file to a local
      // cache after generating it.
      // $pdf_header = \Drupal::config('ocha_snap.settings')->get('header');.
      $pdf_header = $this->configFactory->get('ocha_snap.settings')->get('header');
      if ($css = $this->configFactory->get('ocha_snap.settings')->get('css')) {
        $pdf_header .= '<style type="text/css">' . $css . '</style>';
      }
      $params = [
        'debug'        => (getenv("PHP_ENVIRONMENT") == "development") ? TRUE : FALSE,
        'media'        => 'print',
        'output'       => 'pdf',
        'service'      => 'gms',
        'pdfLandscape' => 'true',
        'pdfMarginRight' => '20',
        'pdfMarginLeft' => '20',
        'pdfMarginUnit' => 'px',
        'pdfHeader'     => $pdf_header,
      ];

      $url = Url::fromUri("base:{$entity_type}/{$entity_id}")->setAbsolute(TRUE)->toString() . "?menu_visibility=show";
      $pdf = ocha_snap($url, $params);
      if (empty($pdf)) {
        $this->messenger()->addMessage($this->t('Failed to generate a PDF file.'), 'error');
        $response = new RedirectResponse($url, 301);
        $response->send();
      }
      else {
        $response = new Response();
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Content-type', 'application/pdf; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Cache-control', 'private');
        $response->headers->set('Content-length', strlen($pdf));
        $response->setContent($pdf);
        $response->send();
      }
    }
    else {
      global $base_url;
      $this->messenger()->addMessage($this->t('Access denied.'), 'error');
      $response = new RedirectResponse($base_url, 301);
      $response->send();
    }
  }

}
