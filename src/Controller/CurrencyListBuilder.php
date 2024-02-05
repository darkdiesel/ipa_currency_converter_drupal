<?php

namespace Drupal\ipa_currency_converter\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of currency entities.
 */
class CurrencyListBuilder extends EntityListBuilder
{

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface    $entityType,
    EntityStorageInterface $storage,
    FormBuilderInterface   $formBuilder,
  )
  {
    parent::__construct($entityType, $storage);

    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface  $container,
    EntityTypeInterface $entity_type,
  )
  {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getModuleName()
  {
    return 'ipa_currency_converter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader()
  {
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Label');
    $header['chart_code'] = $this->t('Code');
    $header['rate'] = $this->t('Rate');
    $header['enabled'] = $this->t('Enabled');
    $header['base'] = $this->t('Base Currency');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity)
  {
    $row['id'] = $entity->id();
    $row['chart_code'] = $entity->getChartCode();
    $row['label'] = $entity->label();
    $row['tate'] = $entity->getRate();
    $row['enabled'] = $entity->getEnabled() ? $this->t('Yes') : $this->t('No');
    $row['base'] = $entity->getBase() ? $this->t('Yes') : $this->t('No');


    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render()
  {
    $build = parent::render();

    // Create an instance of the CurrencyBaseSelectForm. It contains
    // configuration fields.
//    $form = $this->formBuilder->getForm(
//      'Drupal\convert_currencies\Form\CurrencyConfigForm'
//    );
//
//    // Add the form to the $build array.
//    $build['currency_config'] = $form;

    return $build;
  }

}
