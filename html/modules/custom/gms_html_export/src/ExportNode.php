<?php
namespace Drupal\gms_html_export;


use Drupal\node\Entity\Node;

class ExportNode {

  public static function html_export_render_pages($paths, &$context){
    $message = 'Exporting Node...';
    $results = array();
//    foreach ($paths as $nid) {
//      $node = Node::load($nid);
//      $results[] = []; // $node->delete();
//    }
    $context['message'] = $message;
    $context['results'] = $results;
  }
  public static function html_export_copy_all_resources($paths, &$context){
    $message = 'Exporting2 Node...';
    $results = array();
//    foreach ($paths as $nid) {
//      $node = Node::load($nid);
//      $results[] = []; // $node->delete();
//    }
    $context['message'] = $message;
    $context['results'] = $results;
  }
  public static function html_export_cleanup($paths, &$context){
    $message = 'Exporting3 Node...';
    $results = array();
//    foreach ($paths as $nid) {
//      $node = Node::load($nid);
//      $results[] = []; // $node->delete();
//    }
    $context['message'] = $message;
    $context['results'] = $results;
  }
  function exportNodeExampleFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    \Drupal::messenger()->addMessage($message);
  }
}
