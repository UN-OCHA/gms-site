<?php
namespace Drupal\gms_ocha;

define('GMS_OCHA_HIGHMAP', 'http://api.openweathermap.org/data/2.5/weather?id=5128581&units=imperial');
define('GMS_OCHA_ADMIN_PARENT', 457);
define('GMS_OCHA_STAG_POOLFUND', 'http://staging03.trigyn.com:8080/gmsapi/v1/poolfund.json');
define('GMS_OCHA_STAG_POOLFUND_COLLECTION', 'http://staging03.trigyn.com:8080/gmsapi/v1/poolfund/distribution.json');
// @codingStandardsIgnoreLine
// define('GMS_OCHA_PROD_POOLFUND','http://gms.unocha.org/api/v1/poolfund.json');
// @codingStandardsIgnoreLine
// define('GMS_OCHA_PROD_POOLFUND_COLLECTION','http://gms.unocha.org/api/v1/poolfund/distribution.json');
define('GMS_OCHA_PROD_POOLFUND', 'https://cbpfapi.unocha.org/v1/v1/poolfund.json');
define('GMS_OCHA_PROD_POOLFUND_COLLECTION', 'https://cbpfapi.unocha.org/v1/v1/poolfund/distribution.json');

define('GMS_OCHA_STAG_POOLFUND_CONTRIBUTION', 'http://staging03.trigyn.com:8080/vo1/odata/Contribution');
define('GMS_OCHA_PROD_POOLFUND_CONTRIBUTION', 'http://cbpfapi.unocha.org/vo1/odata/Contribution');
define('GMS_OCHA_STAG_POOLFUND_PROJECTSUMMARY', 'http://staging03.trigyn.com:8080/vo1/odata/ProjectSummary');
define('GMS_OCHA_PROD_POOLFUND_PROJECTSUMMARY', 'http://cbpfapi.unocha.org/vo1/odata/ProjectSummary');
define('GMS_OCHA_STAG_POOLFUND_NARRATIVEREPORTSUMMARY', 'http://staging03.trigyn.com:8080/vo1/odata/NarrativeReportSummary');
define('GMS_OCHA_PROD_POOLFUND_NARRATIVEREPORTSUMMARY', 'http://cbpfapi.unocha.org/vo1/odata/NarrativeReportSummary');

use Drupal\Component\Serialization\Json;

/**
 * Class graphData.
 */
class graphData {

  /**
   * Constructs a new graphData object.
   */
  public function __construct() {
  }

  function gmsOchaGetPoolfundYearwise($year_array = array()) {
    $rows = $country_array = $donors_array = array();
    $cached =  \Drupal::cache()->get('Poolfund_project_sankey_chart_' . implode('_', $year_array));
    $data = isset($cached->data) ? $cached->data : '';
    $donors_array =  \Drupal::cache()->get('Poolfund_project_sankey_chart_donors_' . implode('_', $year_array));
    $donors_array = isset($donors_array->data) ? $donors_array->data : array();
    $country_array =  \Drupal::cache()->get('Poolfund_project_sankey_chart_country_' . implode('_', $year_array));
    $country_array = isset($country_array->data) ? $country_array->data : array();
    if (empty($data)) {
      foreach ($year_array as $ykey => $yvalue) {
        $cached =  \Drupal::cache()->get('Poolfund_project_sankey_chart_response_' . $yvalue);
        $poolfund_response = isset($cached->data) ? $cached->data : '';
        if (empty($poolfund_response)) {
          $url = GMS_OCHA_PROD_POOLFUND_CONTRIBUTION;
          $url .= '?year=' . $yvalue;
          $headers = [];
          try {
            $client = \Drupal::httpClient();
            $request = $client->request('POST',$url,$headers);
          }
          catch (RequestException $e){
            // Log the error.
            watchdog_exception('gms_ocha Http Request', $e);
          }
          $responseStatus = $request->getStatusCode();
          $response_data = $request->getBody()->getContents();
          $poolfund_response = json_decode($response_data, true);
          \Drupal::cache()->set('Poolfund_project_sankey_chart_response_' . $yvalue, $poolfund_response);
        }
        foreach ($poolfund_response['value'] as $key => $value) {
          if (array_key_exists($value['PooledFundCodeAbbrv'], $rows)) {
            if (array_key_exists($value['GMSDonorID'], $rows[$value['PooledFundCodeAbbrv']]['donor'])) {
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['name'] = $value['GMSDonorName'];
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['id'] = $value['CountryCode'];
              $amount_paid = ($value['PaidAmt'] > 0) ? $value['PaidAmt'] : 0;
              if ($amount_paid == 0) {
                $pladge_year = date('Y', strtotime($value['PledgeDate']));
                if ($pladge_year >= $yvalue) {
                  $amount_pledge = $value['PledgeAmt'];
                }
              }
              $amount = ($amount_paid > 0) ? $amount_paid : $amount_pledge;
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['amount'] += $amount;
            }
            else {
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['name'] = $value['GMSDonorName'];
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['id'] = $value['CountryCode'];
              $amount_paid = ($value['PaidAmt'] > 0) ? $value['PaidAmt'] : 0;
              if ($amount_paid == 0) {
                $pladge_year = date('Y', strtotime($value['PledgeDate']));
                if ($pladge_year >= $yvalue) {
                  $amount_pledge = $value['PledgeAmt'];
                }
              }
              $amount = ($amount_paid > 0) ? $amount_paid : $amount_pledge;
              $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['amount'] = $amount;
            }
          }
          else {
            $rows[$value['PooledFundCodeAbbrv']]['name'] = $value['PooledFundName'];
            $rows[$value['PooledFundCodeAbbrv']]['id'] = $value['PooledFundId'];
            $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['id'] = $value['CountryCode'];
            $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['name'] = $value['GMSDonorName'];
            $amount_paid = ($value['PaidAmt'] > 0) ? $value['PaidAmt'] : 0;
            if ($amount_paid == 0) {
              $pladge_year = date('Y', strtotime($value['PledgeDate']));
              if ($pladge_year >= $yvalue) {
                $amount_pledge = $value['PledgeAmt'];
              }
            }
            $amount = ($amount_paid > 0) ? $amount_paid : $amount_pledge;
            $rows[$value['PooledFundCodeAbbrv']]['donor'][$value['GMSDonorID']]['amount'] = $amount;
          }
          $donors_array[$value['GMSDonorID']] = $value['GMSDonorName'];
          $country_array[$value['PooledFundCodeAbbrv']] = $value['PooledFundName'];
        }
      }
      \Drupal::cache()->set('Poolfund_project_sankey_chart_donors_' . implode('_', $year_array), $donors_array);
      \Drupal::cache()->set('Poolfund_project_sankey_chart_country_' . implode('_', $year_array), $country_array);
      \Drupal::cache()->set('Poolfund_project_sankey_chart_' . implode('_', $year_array), $rows);
    }
    return array($country_array, $donors_array);
  }

  function gmsOchaGetDonation($year = NULL) {
    $cached = \Drupal::cache()->get('Poolfund_donation_' . $year);
    $data = isset($cached->data) ? $cached->data : '';
    if (empty($data)) {
      $headers = array();
      $url = GMS_OCHA_PROD_POOLFUND_CONTRIBUTION;
      if ($year != NULL) {
        $url .= '?year=' . $year;
      }
      try {
        $client = \Drupal::httpClient();
        $request = $client->request('POST',$url,$headers);
      }
      catch (RequestException $e){
        // Log the error.
        watchdog_exception('gms_ocha Http Request', $e);
      }
      $responseStatus = $request->getStatusCode();
      $response_data = $request->getBody()->getContents();
      $data = array();
      if ($responseStatus == 200) {
        $poolfund_response = json_decode($response_data, true);
        foreach ($poolfund_response['value'] as $key => $value) {
          if (!empty($value['PooledFundCodeAbbrv']) && $value['PooledFundCodeAbbrv'] != 'null') {
            $amount_paid = ($value['PaidAmt'] > 0) ? $value['PaidAmt'] : 0;
            if ($amount_paid == 0) {
              $pladge_year = date('Y', strtotime($value['PledgeDate']));
              if ($pladge_year >= $value['FiscalYear']) {
                $amount_pledge = $value['PledgeAmt'];
              }
            }
            if (isset($data[$value['FiscalYear']]['amt'])) {
              $data[$value['FiscalYear']]['amt'] += ($amount_paid > 0) ? $amount_paid : $amount_pledge;
            }
            else {
              $data[$value['FiscalYear']]['amt'] = ($amount_paid > 0) ? $amount_paid : $amount_pledge;
            }
            if ($data[$value['FiscalYear']]['amt'] > 0) {
              $data[$value['FiscalYear']]['donor'][$value['GMSDonorID']] = $value['GMSDonorName'];
            }
            $data[$value['FiscalYear']]['countries'][$value['PooledFundCodeAbbrv']] = $value['PooledFundName'];
          }
        }
      }
      \Drupal::cache()->set('Poolfund_donation_' . $year, $data);
    }
    return $data;
  }

  function gmsOchaGetProjectSummaryByYear() {
    $host = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBasePath();
    $url = $host . '/sites/default/files/ProjectSummary.json';
    $headers = ['Accept' => 'application/json; charset=utf-8'];
    try {
      $client = \Drupal::httpClient();
      $request = $client->request('GET',$url,$headers);
    }
    catch (RequestException $e){
      // Log the error.
      watchdog_exception('gms_ocha JSON Request', $e);
    }
    $responseStatus = $request->getStatusCode();
    $response_data = $request->getBody()->getContents();
    $results = json_decode($response_data, true);
//    dump($results);die;
    // Narrative Report Summary
    // $url_narrative = GMS_OCHA_PROD_POOLFUND_NARRATIVEREPORTSUMMARY;
    $url_narrative = $host . '/sites/default/files/NarrativeReportSummary.json';
    $headers = ['Accept' => 'application/json; charset=utf-8'];
    try {
      $client_narrative = \Drupal::httpClient();
      $request_narrative = $client_narrative->request('GET',$url_narrative,$headers);
    }
    catch (RequestException $e){
      // Log the error.
      watchdog_exception('gms_ocha JSON Request', $e);
    }
    $responseStatus = $request_narrative->getStatusCode();
    $response_data_narrative = $request_narrative->getBody()->getContents();
    $results_narrative = json_decode($response_data_narrative, true);
    $actual_beneficiaries_total = array();
    foreach ($results_narrative->value as $key_narrative => $value_narrative) {
      if (isset($actual_beneficiaries_total[$value_narrative->AllocationYear][$value_narrative->PooledFundName])) {
        $actual_beneficiaries_total[$value_narrative->AllocationYear][$value_narrative->PooledFundName]['ActualBeneficiariesTotal'] += $value_narrative->ActualBeneficiariesTotal;
      }
      else {
        $actual_beneficiaries_total[$value_narrative->AllocationYear][$value_narrative->PooledFundName]['ActualBeneficiariesTotal'] = $value_narrative->ActualBeneficiariesTotal;
      }
    }

    for ($i = 2; $i >= 0; $i--) {
      $y = date("Y", strtotime("-" . $i . " year"));

      $cached = \Drupal::cache()->get('Poolfund_project_summary_' . $y);
      $data = isset($cached->data) ? $cached->data : '';
      if (empty($data)) {
        $data = $orgnization = $all_orgnization = $poolfundname = array();
        // $data['budget'][$y] = $data['beneficiaries'] = 0;
        foreach ($results->value as $key => $value) {
          if ($value->AllocationYear == $y) {
            $data['budget'][$value->AllocationYear] = $data['budget'][$value->AllocationYear] + $value->Budget;
            $data['beneficiaries'] = $data['beneficiaries'] + $value->Men;
            $data['beneficiaries'] = $data['beneficiaries'] + $value->Women;
            $data['beneficiaries'] = $data['beneficiaries'] + $value->Boys;
            $data['beneficiaries'] = $data['beneficiaries'] + $value->Girls;
            $all_orgnization[] = $value->OrganizationName;
            $orgnization[$value->OrganizationName] = $value->OrganizationName;
            if (isset($actual_beneficiaries_total[$y][$value->PooledFundName])) {
              $poolfundname[$y][$value->PooledFundName] = $actual_beneficiaries_total[$y][$value->PooledFundName]['ActualBeneficiariesTotal'];
            }
          }
        }
        $data['partners_funded'] = count($orgnization);
        $data['projects_funded'] = count($all_orgnization);
        $data['beneficiaries_reached'] = array_sum($poolfundname[$y]);
        \Drupal::cache()->set('Poolfund_project_summary_' . $y, $data);
      }
    }
    return $data;
  }


}
