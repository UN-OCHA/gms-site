<?php

namespace Drupal\gms_ocha\Form;

use Drupal\Core\Cache\CacheFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\gms_ocha\GraphData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Widget form.
 */
class PooledFundWidgetForm extends FormBase {

  /**
   * The query.
   *
   * @var Drupal\gms_ocha\GraphData
   */
  private $graphData;
  /**
   * The query.
   *
   * @var Drupal\Core\Cache\CacheFactory
   */
  private $cache;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param Drupal\gms_ocha\GraphData $graphData
   *   The GraphData service.
   * @param Drupal\Core\Cache\CacheFactory $cache
   *   The Cache service.
   */
  public function __construct(GraphData $graphData, CacheFactory $cache) {
    $this->graphData = $graphData;
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gms_ocha.graph_data'),
      $container->get('cache_factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gms_ocha_pooled_fund_widget_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $years = [];
    for ($i = 2; $i >= 0; $i--) {
      $y = date("Y", strtotime("-" . $i . " year"));
      $years[$y] = $y;
    }
    $donation = $project_summary = 0;
    $userInput = $form_state->getUserInput();
    if (!empty($userInput['poolfund_years'])) {
      $years = array_filter($userInput['poolfund_years']);
      if (isset($years['all'])) {
        $years = [];
        for ($i = 2; $i >= 0; $i--) {
          $y = date("Y", strtotime("-" . $i . " year"));
          $years[$y] = $y;
        }
      }
    }
    $budget = $beneficiaries = $projects_funded = $partners_funded = $beneficiaries_reached = $donation = 0;
    $donors = $countries = [];
    foreach ($years as $year_value) {
      $donations = $this->graphData->gmsOchaGetDonation($year_value);
      $donation += $donations[$year_value]['amt'];
      $donors = $donors + $donations[$year_value]['donor'];
      $countries = $countries + $donations[$year_value]['countries'];
      $cached = $this->cache->get('Poolfund_project_summary_' . $year_value);
      if (!isset($cached->data)) {
        $this->graphData->gmsOchaGetProjectSummaryByYear();
        $cached = $this->cache->get('Poolfund_project_summary_' . $year_value);
      }
      $budget += $cached->data['budget'][$year_value];
      $beneficiaries += $cached->data['beneficiaries'];
      $projects_funded += $cached->data['projects_funded'];
      $partners_funded += $cached->data['partners_funded'];
      $beneficiaries_reached += $cached->data['beneficiaries_reached'];
    }
    $values = [
      'donation' => [
        'title' => '$ Donation',
        'value' => '$' . round(($donation / 1000000), 2) . ' M',
      ],
      'allocations' => [
        'title' => '$ Allocations',
        'value' => '$' . round(($budget / 1000000), 2) . ' M',
      ],
      'donors' => [
        'title' => '# Donors',
        'value' => count($donors),
      ],
      'countries' => [
        'title' => '# Countries',
        'value' => count($countries),
      ],
      'target' => [
        'title' => '# Beneficiaries Targeted',
        'value' => round(($beneficiaries / 1000000), 2) . ' M',
      ],
      'reached' => [
        'title' => '# Beneficiaries Reached',
        'value' => round(($beneficiaries_reached / 1000000), 2) . ' M',
      ],
      'projects' => [
        'title' => '# Projects Funded',
        'value' => $projects_funded,
      ],
      'partners' => [
        'title' => '# Partners Funded',
        'value' => $partners_funded,
      ],
    ];
    // The options to display in our checkboxes.
    $options = ['all' => 'All'];
    for ($i = 2; $i >= 0; $i--) {
      $y = date("Y", strtotime("-" . $i . " year"));
      $options[$y] = $y;
    }
    // The drupal checkboxes form field definition.
    $form['poolfund_years'] = [
      '#title' => '',
      '#type' => 'checkboxes',
      '#options' => $options,
	  '#prefix' => '<div id="pooled-checkboxes" class="pooled-checkboxes">',
      '#suffix' => '</div>',
      '#default_value' => ['all'],
      '#ajax' => [
        'path' => 'gms_ocha/ajax',
        'callback' => 'gms_ocha_poolfund_callback',
        'wrapper' => 'pooled-fund-badges',
        'method' => 'replace',
        'effect' => 'fade',
      ],
    ];
    $sting = $this->generateHtml($values);
    $form['poolfund_badges'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="pooled-fund-badges" class="pooled-fund-badges">',
      '#suffix' => '</div>',
      '#markup' => $sting,
    ];
	$form['#prefix'] = '<div id="pooled-fund-container" class="cd-container">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  private function generateHtml($values) {
    $html = '<div class="cd-grid">
    <div class="pooled-fund-donation">
      <span class="title">' . $values['donation']['title'] . '</span>
      <span class="value">' . $values['donation']['value'] . '</span>
    </div>
    <div class="pooled-fund-allocations">
      <span class="title">' . $values['allocations']['title'] . '</span>
      <span class="value">' . $values['donation']['value'] . '</span>
    </div>
    <div class="pooled-fund-donors">
      <span class="title">' . $values['donors']['title'] . '</span>
      <span class="value">' . $values['donors']['value'] . '</span>
    </div>
    <div class="pooled-fund-countries">
      <span class="title">' . $values['countries']['title'] . '</span>
      <span class="value">' . $values['countries']['value'] . '</span>
    </div>
  </div>
  <div class="cd-grid">
    <div class="pooled-fund-target">
      <span class="title">' . $values['target']['title'] . '</span>
      <span class="value">' . $values['target']['value'] . '</span>
    </div>
    <div class="pooled-fund-reached">
      <span class="title">' . $values['reached']['title'] . '</span>
      <span class="value">' . $values['reached']['value'] . '</span>
    </div>
    <div class="pooled-fund-projects">
      <span class="title">' . $values['projects']['title'] . '</span>
      <span class="value">' . $values['projects']['value'] . '</span>
    </div>
    <div class="pooled-fund-partners">
       <span class="title">' . $values['partners']['title'] . '</span>
      <span class="value">' . $values['partners']['value'] . '</span>
    </div>
  </div>';
    return $html;
  }

}
