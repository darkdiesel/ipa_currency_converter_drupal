<?php

namespace Drupal\ipa_currency_converter\Form;

use Drupal\Component\Utility\Environment;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure currency api
 */
class CurrencyConverterSettingsForm extends ConfigFormBase {

  /**
   * Uniq key to store settings for module
   *
   * @var string
   */
  const SETTINGS_KEY = 'ipa.currency_converter.settings';

  const API_KEY_SETTING = 'freecurrencyapicom_api_key';
  const LAST_RATE_UPDATED_SETTING = 'last_time_rate_updated';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipa_currency_converter_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS_KEY,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $configs = $this->config(static::SETTINGS_KEY);

    $form['ipa_currency_converter_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('IPA currency converter settings'),
    ];

    $form['ipa_currency_converter_settings']['freecurrencyapicom_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#description' => $this->t('Api key for freecurrencyapi.com service'),
      '#required' => TRUE,
      '#default_value' => $configs->get(self::API_KEY_SETTING) ?? '',
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS_KEY)
      ->set(self::API_KEY_SETTING, $form_state->getValue('freecurrencyapicom_api_key'))
      ->save();


    parent::submitForm($form, $form_state);
  }

}
