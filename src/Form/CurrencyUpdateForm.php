<?php

namespace Drupal\ipa_currency_converter\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ipa_currency_converter\Controller\CurrencyConverterController;

/**
 * Defines the content import form.
 */
class CurrencyUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ipa_currency_converter__update_rate__form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container'] = [
      '#type'  => 'fieldset',
      '#title' => $this->t('Update currency rates'),
    ];

    $last_time = CurrencyConverterController::getLastTimeRateUpdated();

    $form['container']['last-time-container'] = [
      '#type' => 'fieldgroup',
    ];

    if ($last_time) {
      $form['container']['last-time-container'][] = [
        '#type'          => 'label',
        '#title'         => t('Time of last currency rates updates'),
        '#title_display' => t('Time of last currency rates updates'),
      ];

      $form['container']['last-time-container'][] = [
        '#type'   => 'text',
        '#markup' => \Drupal::service('date.formatter')
                            ->format($last_time, 'custom', 'Y-m-d H:i:s'),
      ];
    }
    else {
      $form['container']['last-time-container'][] = [
        '#type'          => 'label',
        '#title'         => t(
          'No updates made yet. Press Update button to sync rates from API'
        ),
        '#title_display' => t(
          'No updates made yet. Press Update button to sync rates from API'
        ),
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Update'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $result = CurrencyConverterController::updateRates();

    if ($result) {
      \Drupal::messenger()
             ->addStatus(
               $this->t('You successfully update currency rates.')
             );
    }
    else {
      \Drupal::messenger()
             ->addError(
               $this->t(
                 'Some error occurred, please check reports -> recent log messages to find problem and fix it.'
               )
             );
    }
  }

}
