<?php

namespace Drupal\ipa_currency_converter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ipa_currency_converter\Entity\Currency;
use Drupal\ipa_currency_converter\Form\CurrencyConverterSettingsForm;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class CurrencyConverterController extends ControllerBase {

  private const BASE_API_URL = 'https://api.freecurrencyapi.com';

  private const LATEST_API_URI = '/v1/latest';

  /**
   * Convert value from first currency to second currency
   *
   * @param int $value
   * @param string $currencyFrom
   * @param string $currencyTo
   *
   * @return false|void
   */
  public static function convert(int $value, string $currencyFrom, string $currencyTo) {
    $query = \Drupal::entityQuery('ipa_currency_converter_currency')
                    ->accessCheck(TRUE);

    $orGroup1 = $query->orConditionGroup()
                      ->condition(
                        'chart_code',
                        [$currencyFrom, $currencyTo],
                        'IN'
                      )
                      ->condition('base', 1);

    $query->condition($orGroup1)->condition('enabled', 1);

    $currencyEntities = $query->execute();

    $baseCurrency = NULL;

    $currencyEntities = Currency::loadMultiple($currencyEntities);

    $currencyFromEntity = null;
    $currencyToEntity = null;

    foreach ($currencyEntities as $currencyEntity) {
      /**
       * @var $currencyEntity Currency
       */
      switch ($currencyEntity->getChartCode()) {
        case $currencyFrom:
          /**
           * @var $currencyFromEntity Currency
           */
          $currencyFromEntity = $currencyEntity;
          break;
        case $currencyTo:
          /**
           * @var $currencyToEntity Currency
           */
          $currencyToEntity = $currencyEntity;
          break;
      }

      if ($currencyEntity->getBase()) {
        $baseCurrency = $currencyEntity;
      }
    }

    if ( ! $baseCurrency) {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Base currency not configured, please setup base currency to continue updating rates.'
               )
             );

      return FALSE;
    }

    // convert currency from to base currency rate
    if ($currencyFromEntity) {
      $_currencyFrom = $baseCurrency->getRate() / $currencyFromEntity->getRate();
    } else {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Currency %currency for converting not exist. Add it and update rates for using converting',
                 ['%currency' => $currencyFrom]
               )
             );

      return FALSE;
    }

    // convert second currency to base currency rate
    if ($currencyToEntity) {
      $_currencyTo = $baseCurrency->getRate() / $currencyToEntity->getRate();
    } else {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Currency %currency for converting not exist. Add it and update rates for using converting',
                 ['%currency' => $currencyTo]
               )
             );

      return FALSE;
    }

    return $value * ($_currencyFrom / $_currencyTo);
  }

  public static function updateRates() {
    $apiKey = \Drupal::configFactory()->get(
      CurrencyConverterSettingsForm::SETTINGS_KEY
    )->get(CurrencyConverterSettingsForm::API_KEY_SETTING);

    if ( ! $apiKey) {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Api key not configured. Please add api key to sync rates from API.'
               )
             );

      return FALSE;
    }

    $query = \Drupal::entityQuery('ipa_currency_converter_currency')
                    ->accessCheck(TRUE)
                    ->condition('enabled', 1)
                    ->execute();

    $currencyEntities = Currency::loadMultiple($query);

    if ( ! count($currencyEntities)) {
      \Drupal::logger('ipa_currency_converter')
             ->error(t("Currencies not found. Please add currencies."));

      return FALSE;
    }

    $baseCurrency = NULL;
    $currencies   = [];

    foreach ($currencyEntities as $currencyEntity) {
      /**
       * @var $currencyEntity Currency
       */
      $currencies[] = $currencyEntity->getChartCode();

      if ($currencyEntity->getBase()) {
        $baseCurrency = $currencyEntity->getChartCode();
      }
    }

    if ( ! $baseCurrency) {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Base currency not configured, please setup base currency to continue updating rates.'
               )
             );

      return FALSE;
    }

    $uri = sprintf(
      "%s%s?apikey=%s&base_currency=%s&currencies=%s",
      self::BASE_API_URL,
      self::LATEST_API_URI,
      $apiKey,
      $baseCurrency,
      implode(',', $currencies)
    );

    try {
      $response = \Drupal::httpClient()->get($uri, [
        'verify' => FALSE,
      ]);

      $response = $response->getBody();
    }
    catch (\Exception $e) {
      \Drupal::logger('ipa_currency_converter')
             ->error(
               t(
                 'Something went wrong during updating currency rates from API. Please check your data or contact site administrator.'
               )
             );
      \Drupal::logger('ipa_currency_converter')->error($e->getMessage());

      return FALSE;
    }

    $json_encoder = new JsonEncoder();
    $serializer   = new Serializer([], [$json_encoder]);
    $format       = 'json';

    $dataResponse = $serializer->decode($response, $format);

    if (isset($dataResponse['data'])) {
      foreach ($dataResponse['data'] as $currencyCode => $currencyRate) {
        $currencyEntities = \Drupal::entityQuery(
          'ipa_currency_converter_currency'
        )
                                   ->condition('chart_code', $currencyCode)
                                   ->accessCheck(TRUE)
                                   ->execute();

        if (count($currencyEntities)) {
          $currencyEntity = array_shift($currencyEntities);

          $currencyEntity = Currency::load($currencyEntity);

          $currencyEntity->setRate($currencyRate);
          $currencyEntity->save();
        }
      }
    }

    self::setLastTimeRateUpdated(time());

    return TRUE;
  }

  /**
   * Return last time when currencies rete was updated
   *
   * @return array|mixed|null
   */
  public static function getLastTimeRateUpdated() {
    return \Drupal::configFactory()->get(
      CurrencyConverterSettingsForm::SETTINGS_KEY
    )->get(CurrencyConverterSettingsForm::LAST_RATE_UPDATED_SETTING);
  }

  public static function setLastTimeRateUpdated($time) {
    return \Drupal::configFactory()
                  ->get(
                    CurrencyConverterSettingsForm::SETTINGS_KEY
                  )
                  ->set(
                    CurrencyConverterSettingsForm::LAST_RATE_UPDATED_SETTING,
                    $time
                  )
                  ->save();
  }

}
