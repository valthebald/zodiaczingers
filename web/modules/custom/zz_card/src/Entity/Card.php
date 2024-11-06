<?php

namespace Drupal\zz_card\Entity;


use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the node entity class.
 *
 * @ContentEntityType(
 *   id = "zz_card",
 *   label = @Translation("Horoscope card"),
 *   label_collection = @Translation("Horoscope cards"),
 *   label_singular = @Translation("card"),
 *   label_plural = @Translation("cards"),
 *   label_count = @PluralTranslation(
 *     singular = "@count card",
 *     plural = "@count cards"
 *   ),
 *   bundle_label = @Translation("Content type"),
 *   handlers = {
 *     "storage" = "Drupal\zz_card\CardStorage"
 *   },
 *   base_table = "zz_card",
 *   data_table = "zz_card_data",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/zz_card/{zz_card}",
 *     "delete-form" = "/zz_card/{zz_card}",
 *     "edit-form" = "/zz_card/{zz_card}/edit",
 *     "create" = "/zz_card",
 *   }
 * )
 */
class Card extends ContentEntityBase implements EntityPublishedInterface {

  use EntityPublishedTrait;

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *
   * @return array|\Drupal\Core\Field\FieldDefinitionInterface[]
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += self::publishedBaseFieldDefinitions($entity_type);
    // Initial value is needed because status field didn't exist initially.
    $fields['status']->setInitialValue(TRUE)
      ->setDefaultValue(TRUE);
    $fields['sign'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Zodiac sign'))
      ->setDescription(t('The zodiac sign.'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'region' => 'hidden',
        'weight' => -5,
      ])
      ->addConstraint('ZodiacSign', [])
      ->setTranslatable(FALSE);
    $fields['card_date'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Zodiac sign'))
      ->setDescription(t('The zodiac sign.'))
      ->setTranslatable(FALSE);
    $fields['content'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Horoscope content'))
      ->setTranslatable(TRUE);
    return $fields;
  }

}
