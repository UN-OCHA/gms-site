<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides a RightSide Menu block.
 *
 * @Block(
 *   id = "rightside_menu",
 *   admin_label = @Translation("RightSide Menu")
 * )
 */
class RightSideMenu extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

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
   * The config factory object.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $serviceData;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Entity Manager call.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer, EntityTypeManagerInterface $entity_type_manager, AliasManagerInterface $alias_manager, RequestStack $request, ConfigFactoryInterface $config_factory, $serviceData, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
    $this->aliasManager = $alias_manager;
    $this->request = $request;
    $this->configFactory = $config_factory;
    $this->serviceData = $serviceData;
    $this->database = $database;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A configuration array containing information about the plugin instance.
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin Id.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $container->get('path_alias.manager'),
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container,
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_path = $this->serviceData->get('path.current')->getPath();
    $result = $this->aliasManager->getAliasByPath($current_path);
    $query = $this->entityTypeManager->getStorage('path_alias')->getQuery();
    $query->condition('alias', $result, '=');
    $aliasIds = $query->execute();
    $aliasIds = array_values($aliasIds);
    if (!empty($aliasIds)) {
      $path_var = $this->entityTypeManager->getStorage('path_alias')->load($aliasIds[0]);
      $path = $path_var->getPath();
      $nodeId = (int) str_replace("/node/", "", $path);
      $query = $this->database->select('menu_tree', 'menu_tree')
        ->fields('menu_tree', ['id', 'parent'])
        ->condition('route_param_key', "node=" . $nodeId)
        ->condition('menu_name', 'menu-ocha');
      $results = $query->execute();
      $menuData = $results->fetchAssoc();
      $menuId = NULL;
      if (isset($menuData['parent']) && empty($menuData['parent'])) {
        $menuId = str_replace('menu_link_content:', '', $menuData['id']);
      }
      $getQuery = $this->request->getCurrentRequest()->query->get('query');
      if (!empty($getQuery)) {
        $menuId = $getQuery;
      }
      $menu_name = 'menu-ocha';
      // $menu_tree = \Drupal::menuTree();
      $menu_tree = $this->serviceData->get('menu.link_tree');
      $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
      $parameters->setMinDepth(2);
      $parameters->setMaxDepth(NULL);
      $tree = $menu_tree->load($menu_name, $parameters);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ];
      $treeOld = $menu_tree->transform($tree, $manipulators);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
        ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ];
      $tree = $menu_tree->transform($tree, $manipulators);
      $menu = $menu_tree->build($tree);
      foreach ($menu['#items'] as $key => $val) {
        if (!empty($menuId)) {
          $val['url']->setOption("query", ['query' => $menuId]);
        }
        if (isset($val['below']) && !empty($val['below'])) {
          foreach ($val['below'] as $bVal) {
            $bVal['url']->setOption("query", ['query' => $menuId]);
          }
        }
        $menu['#items'][$key]['is_expandable'] = $treeOld[$key]->hasChildren;
      }
      $menu_html = $this->renderer->render($menu);
    }
    else {
      $menu_html = '';
    }
    $build = [
      '#theme' => 'gms_ocha_rightsidemenu',
      '#menu_html' => $menu_html,
      '#attached' => [
        'library' => [
          'gms_ocha/menu_portal',
        ],
      ],
      '#cache' => ['max-age' => 0],
    ];
    return $build;
  }

}
