<?php

namespace Drupal\gms_ocha\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;

/**
 * Widget form.
 */
class PooledFundWidgetForm extends FormBase {

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
    if (!empty($form_state->getValue('poolfund_years'))) {
      $years = array_filter($form_state->getValue('poolfund_years'));
      $years = array_filter($form_state->getValue('poolfund_years'));
      if (isset($years['all'])) {
        $years = [];
        for ($i = 2; $i >= 0; $i--) {
          $y = date("Y", strtotime("-" . $i . " year"));
          $years[$y] = $y;
        }
      }
    }
    $budget = $beneficiaries = $projects_funded = $partners_funded = $beneficiaries_reached = 0;
    $donors = $countries = [];
    foreach ($years as $year_key => $year_value) {
      $donations = \Drupal::service('gms_ocha.graph_data')->gmsOchaGetDonation($year_value);
      $donation += $donations[$year_value]['amt'];
      $donors = $donors + $donations[$year_value]['donor'];
      $countries = $countries + $donations[$year_value]['countries'];
      $cached = \Drupal::cache()->get('Poolfund_project_summary_' . $year_value);
      if (!isset($cached->data)) {
        \Drupal::service('gms_ocha.graph_data')->gmsOchaGetProjectSummaryByYear();
        $cached = \Drupal::cache()->get('Poolfund_project_summary_' . $year_value);
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
    $html = '<div class="row row-1">
    <div class="pooled-fund-donation col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['donation']['title'] . '</span>
      <span class="value">' . $values['donation']['value'] . '</span>
    </div>
    <div class="pooled-fund-allocations col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['allocations']['title'] . '</span>
      <span class="value">' . $values['donation']['value'] . '</span>
    </div>
    <div class="pooled-fund-donors col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['donors']['title'] . '</span>
      <span class="value">' . $values['donors']['value'] . '</span>
    </div>
    <div class="pooled-fund-countries col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['countries']['title'] . '</span>
      <span class="value">' . $values['countries']['value'] . '</span>
    </div>
  </div>
  <div class="row row-2">
    <div class="pooled-fund-target col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['target']['title'] . '</span>
      <span class="value">' . $values['target']['value'] . '</span>
    </div>
    <div class="pooled-fund-reached col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['reached']['title'] . '</span>
      <span class="value">' . $values['reached']['value'] . '</span>
    </div>
    <div class="pooled-fund-projects col-xs-12 col-sm-12 col-md-3 col-lg-3">
      <span class="title">' . $values['projects']['title'] . '</span>
      <span class="value">' . $values['projects']['value'] . '</span>
    </div>
    <div class="pooled-fund-partners col-xs-12 col-sm-12 col-md-3 col-lg-3">
       <span class="title">' . $values['partners']['title'] . '</span>
      <span class="value">' . $values['partners']['value'] . '</span>
    </div>
  </div>';
    return $html;
  }

}
