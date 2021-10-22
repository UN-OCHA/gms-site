<?php

namespace Drupal\gms_ocha\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\path_alias\Entity\PathAlias;

/**
 * Provides a RightSide Menu block.
 *
 * @Block(
 *   id = "rightside_menu",
 *   admin_label = @Translation("RightSide Menu")
 * )
 */
class RightSideMenu extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_path = \Drupal::service('path.current')->getPath();
    $result = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
    $query = \Drupal::entityQuery('path_alias');
    $query->condition('alias', $result, '=');
    $aliasIds = $query->execute();
    $aliasIds = array_values($aliasIds);
    $path = PathAlias::load($aliasIds[0])->getPath();
    $nodeId = (int) str_replace("/node/", "", $path);
    $query = \Drupal::database()->select('menu_tree', 'menu_tree')
      ->fields('menu_tree', ['id', 'parent'])
      ->condition('route_param_key', "node=" . $nodeId)
      ->condition('menu_name', 'menu-ocha');
    $results = $query->execute();
    $menuData = $results->fetchAssoc();
    $menuId = NULL;
    if (isset($menuData['parent']) && empty($menuData['parent'])) {
      $menuId = str_replace('menu_link_content:', '', $menuData['id']);
    }
    if (isset($_GET['query']) && !empty($_GET['query'])) {
      $menuId = $_GET['query'];
    }
    $menu_name = 'menu-ocha';
    $menu_tree = \Drupal::menuTree();
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
        foreach ($val['below'] as $bKey => $bVal) {
          $bVal['url']->setOption("query", ['query' => $menuId]);
        }
      }
      $menu['#items'][$key]['is_expandable'] = $treeOld[$key]->hasChildren;
    }
    $menu_html = \Drupal::service('renderer')->render($menu);
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
