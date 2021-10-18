<?php

namespace Drupal\gms_pdflink\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an extended Menu block.
 *
 * @Block(
 *   id = "gms_menu_block",
 *   admin_label = @Translation("GMS portal menu block"),
 *   category = @Translation("Menus"),
 * )
 */
class MenusBlock extends BlockBase {

  /**
   * GMS portal menu block.
   */
  public function build() {
    $menuTree = \Drupal::menuTree();
    $menu_name = 'menu-ocha';
    $parameters = $menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    $level = 0;
    $depth = 3;
    $expand_all_items = TRUE;
    $parent = NULL;
    $follow = 0;
    $follow_parent = 'child';
    $following = FALSE;

    $parameters->setMinDepth($level);
    if ($follow && count($parameters->activeTrail) > $level) {
      $level = count($parameters->activeTrail);
      $following = TRUE;
    }

    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $menuTree->maxDepth()));
    }

    $fixed_parent_menu_link_id = str_replace($menu_name . ':', '', $parent);
    if ($following || ($level > 1 && !$fixed_parent_menu_link_id)) {
      if (count($parameters->activeTrail) >= $level) {
        // Active trail array is child-first. Reverse it, and pull the new menu
        // root based on the parent of the configured start level.
        $menu_trail_ids = array_reverse(array_values($parameters->activeTrail));
        $offset = ($following && $follow_parent == 'active') ? 2 : 1;
        $menu_root = $menu_trail_ids[$level - $offset];
        $parameters->setRoot($menu_root)->setMinDepth(1);
        if ($depth > 0) {
          $parameters->setMaxDepth(min($depth, $menuTree->maxDepth()));
        }
      }
      else {
        return [];
      }
    }

    // If expandedParents is empty, the whole menu tree is built.
    if ($expand_all_items) {
      $parameters->expandedParents = [];
    }

    // When a fixed parent item is set, root the menu tree at the given ID.
    if ($fixed_parent_menu_link_id) {
      $fixed_parameters = clone $parameters;
      $fixed_parameters->setRoot($fixed_parent_menu_link_id);
      $tree = $menuTree->load($menu_name, $fixed_parameters);

      // Check if the tree contains links.
      if (empty($tree)) {
        if ($this->configuration['level'] === 1 || $this->configuration['level'] === '1') {
          $fixed_parameters->expandedParents = [];
          $fixed_parameters->setMinDepth(1);
          $fixed_parameters->setMaxDepth(1);
          $tree = $menuTree->load($menu_name, $fixed_parameters);
        }
      }
      elseif ($following) {
        unset($tree);
      }
    }

    // Load the tree if we haven't already.
    if (!isset($tree)) {
      $tree = $menuTree->load($menu_name, $parameters);
    }
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menuTree->transform($tree, $manipulators);
    $build = $menuTree->build($tree);

    $label = 'GMS Menu Portal Block';
    // Set the block's #title (label) to the dynamic value.
    $build['#title'] = [
      '#markup' => $label,
    ];
    if (!empty($build['#theme'])) {
      // Add the configuration for use in menu_block_theme_suggestions_menu().
      $build['#menu_block_configuration'] = $this->configuration;
      $build['#menu_block_configuration']['label'] = $label;
      $build['#theme'] = 'menu';
    }

    $build['#contextual_links']['menu'] = [
      'route_parameters' => ['menu' => $menu_name],
    ];
    return $build;
  }

}
