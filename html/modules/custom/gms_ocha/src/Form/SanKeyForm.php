<?php

namespace Drupal\gms_ocha\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gms_ocha\GraphData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Sankey chart form.
 */
class SanKeyForm extends FormBase {

  /**
   * The query.
   *
   * @var Drupal\gms_ocha\GraphData
   */
  private $graphData;

  /**
   * Retrieves the currently active request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Creates an DevelLocalTask object.
   *
   * @param Drupal\gms_ocha\GraphData $graphData
   *   The GraphData service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   The request stack.
   */
  public function __construct(GraphData $graphData, RequestStack $request) {
    $this->graphData = $graphData;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gms_ocha.graph_data'),
      $container->get('request_stack'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gms_ocha_donor_sankey_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [
      '#prefix' => '<div id= "pooled-fund-rope"  class="pooled-fund-badges">',
      '#suffix' => '</div>',
    ];

    for ($i = 2; $i >= 0; $i--) {
      $y = date("Y", strtotime("-" . $i . " year"));
      $years[$y] = $y;
    }
    if ($form_state->getValue('poolfund_years')) {
      $years_state = array_filter($form_state->getValue('poolfund_years'));
      if (!isset($years_state['all'])) {
        $years = $years_state;
      }
    }
    [$countries, $donors] = $this->graphData->gmsOchaGetPoolfundYearwise($years);
    $form['message_element'] = [
      '#prefix' => '<div class="container"><div class="row"><div class="poolfund-sankey-title">',
      '#suffix' => '</div>',
      '#markup' => '<h4>DONOR CONTRIBUTIONS VS RECIPIENT COUNTRIES <span class="poolfund-year">• ' . implode(' • ', $years) . '</span></h4>',
    ];
    $form['donor'] = [
      '#type' => 'select',
      '#title' => '',
      '#options' => ['select' => 'Donors'] + $donors,
      '#ajax' => [
        'path' => 'gms_ocha/ajax',
        'callback' => 'gms_ocha_poolfund_donor_country_callback',
        'wrapper' => 'pooled-fund-sankey-chart',
        'method' => 'replace',
        'effect' => 'fade',
      ],
      '#prefix' => '<div class="poolfund-sankey-donor">',
      '#suffix' => '</div>',
    ];
    $form['country'] = [
      '#type' => 'select',
      '#title' => '',
      '#options' => ['select' => 'Countries'] + $countries,
      '#ajax' => [
        'path' => 'gms_ocha/ajax',
        'callback' => 'gms_ocha_poolfund_donor_country_callback',
        'wrapper' => 'pooled-fund-sankey-chart',
        'method' => 'replace',
        'effect' => 'fade',
      ],
      '#prefix' => '<div class="poolfund-sankey-country">',
      '#suffix' => '</div></div>',
    ];
    $string_year = implode('_', $years);
    // $host = \Drupal::request()->getSchemeAndHttpHost()
    // . \Drupal::request()->getBasePath();
    $host = $this->request->getCurrentRequest()->getSchemeAndHttpHost();
    $json_url = $host . "/special/donor/chart/sankey/json/" . $string_year . "/select/select";
    $userInput = $form_state->getUserInput();
    if (isset($userInput['country']) || isset($userInput['donor'])) {
      $json_url = $host . "/special/donor/chart/sankey/json/" . $string_year . "/" . $userInput['country'] . "/" . $userInput['donor'];
    }
    $form['sankey_chart'] = [
      '#type' => 'markup',
      '#markup' => '<div id="chart_main">
                    <div class="sankey-headers">
                        <div class="header-left"><h4>DONORS</h4></div>
                        <div class="header-right"><h4>RECIPIENT COUNTRIES</h4></div>
                    </div>
                    <div id="sankey_chart"></div>
                      </div>',
    ];
    $form['jsonUrl'] = $json_url;
    $form['#attached'] = [
      'library' => ['gms_ocha/sankey_chart'],
      'drupalSettings' => ['sankey_chart' => ['path' => ['sankey' => $json_url]]],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
