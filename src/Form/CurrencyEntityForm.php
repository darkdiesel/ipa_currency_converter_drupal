<?php

namespace Drupal\ipa_currency_converter\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the content_entity_example entity edit forms.
 *
 * @ingroup content_entity_example
 */
class CurrencyEntityForm extends ContentEntityForm
{

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Constructs a new CurrencyBaseForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   */
  public function __construct(EntityStorageInterface $entity_storage)
  {
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity_type.manager')->getStorage('ipa_currency_converter_currency')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form_state->set('langcode', $this->entity->getUntranslated()->language()->getId());

    $form = parent::buildForm($form, $form_state);

    $currency = $this->getEntity();

    $form['chart_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart code'),
      '#maxlength' => 3,
      '#default_value' => $currency->getChartCode(),
      '#required' => TRUE,
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $currency->getLabel(),
      '#required' => TRUE,
    ];

    $form['rate']['#access'] = FALSE;

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $currency->getEnabled(),
      '#required' => false,
    ];

    $form['base'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Base Currency'),
      '#default_value' => $currency->getBase(),
      '#required' => false,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);

    $currency = $this->getEntity();

    if ($currency->isNew()) {
      // check if currency with the same chart code not exist
      $isset = $this->entityStorage->getQuery()
        ->condition('chart_code', $form_state->getValue('chart_code'))
        ->execute();

      if (is_array($isset) && count($isset)) {
        $form_state->setErrorByName(
          'chart_code',
          $this->t('Currency with chart code %code already exist!',
            [
              '%code' => $currency->getChartCode()
            ]
          )
        );
      }
    }

    if ($form_state->getValue('base')) {
      $isset = $this->entityStorage->getQuery()
        ->condition('base', $form_state->getValue('base'))
        ->execute();

      if (is_array($isset) && count($isset) && !in_array($currency->getId(), $isset)) {
        $form_state->setErrorByName(
          'base',
          $this->t('Base currency already configured!')
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state)
  {
    $currency = $this->getEntity();

    $status = $currency->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label currency.', [
        '%label' => $currency->label(),
      ]));
    } else {
      $this->messenger()->addMessage($this->t('The %label currency was not saved.', [
        '%label' => $currency->label(),
      ]), 'error');
    }
    $form_state->setRedirect('entity.ipa_currency_converter_currency.collection');
  }

}
