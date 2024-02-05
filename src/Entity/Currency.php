<?php

namespace Drupal\ipa_currency_converter\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the Currency entity.
 *
 * @ContentEntityType(
 *   id = "ipa_currency_converter_currency",
 *   label = @Translation("Currency"),
 *   module = "ipa_currency_converter",
 *   admin_permission = "administer ipa_currency_converter",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "chart_code" = "chrat_code",
 *     "label" = "label",
 *     "rate" = "rate",
 *     "enabled" = "enabled",
 *     "base" = "base"
 *   },
 *   handlers = {
 *     "list_builder" = "Drupal\ipa_currency_converter\Controller\CurrencyListBuilder",
 *     "form" = {
 *       "default" = "Drupal\ipa_currency_converter\Form\CurrencyEntityForm",
 *       "delete" = "Drupal\ipa_currency_converter\Form\CurrencyEntityDeleteForm",
 *     }
 *   },
 *   base_table = "ipa_currency_converter__currency",
 *   links = {
 *     "collection" = "/admin/config/system/ipa-currency-converter",
 *     "add-form" = "/admin/config/system/ipa-currency-converter/add",
 *     "edit-form" = "/admin/config/system/ipa-currency-converter/{ipa_currency_converter_currency}/edit",
 *     "delete-form" = "/admin/config/system/ipa-currency-converter/{ipa_currency_converter_currency}/delete",
 *   }
 * )
 */
class Currency extends ContentEntityBase implements ContentEntityInterface
{

  /**
   * The currency ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The currency code.
   *
   * @var string
   */
  protected $chart_code;

  /**
   * The currency label.
   *
   * @var string
   */
  protected $label;

  /**
   * The currency rate.
   *
   * @var float
   */
  protected $rate;

  /**
   * The currency enabled.
   *
   * @var float
   */
  protected $enabled;

  /**
   * The currency is base.
   *
   * @var float
   */
  protected $base;

  /**
   * {@inheritdoc}
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getChartCode()
  {
    return $this->chart_code;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel()
  {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getRate()
  {
    return $this->rate;
  }

  /**
   * {@inheritdoc}
   */
  public function setRate($rate)
  {
    $this->set('rate', $rate);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEnabled()
  {
    return $this->enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled)
  {
    $this->set('enabled', $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBase()
  {
    return $this->base;
  }

  /**
   * {@inheritdoc}
   */
  public function setBase($base)
  {
    $this->set('base', $base);
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
  {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['chart_code'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The currency label.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 3);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t('The currency label.'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255);

    $fields['rate'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Base Currency Rate'))
      ->setDescription(t('The base conversion rate of the currency.'))
      ->setDefaultValue(0)
      ->setSettings([
        'scale' => 6,
      ])
      ->setDisplayOptions('form', [
        'region' => 'hidden',
      ])
      ->setTranslatable(FALSE);

      $fields['enabled'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Enabled'))
        ->setDescription(t('A flag indicating that currency enabled for updating and conversation.'))
        ->setRequired(FALSE)
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE)
        ->setDefaultValue(TRUE);

      $fields['base'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Base currency'))
        ->setDescription(t('A flag indicating that currency is base.'))
        ->setRequired(FALSE)
        ->setTranslatable(TRUE)
        ->setRevisionable(TRUE)
        ->setDefaultValue(TRUE);


    return $fields;
  }

}
