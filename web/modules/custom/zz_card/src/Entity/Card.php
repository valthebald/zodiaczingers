<?php

namespace Drupal\zz_card\Entity;


use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\views\EntityViewsData;
use Drupal\zz_card\CardStorage;

/**
 * Defines horoscope card entity class.
 */
#[ContentEntityType(
  id: 'zz_card',
  label: new TranslatableMarkup('Horoscope card'),
  label_collection: new TranslatableMarkup('Horoscope cards'),
  label_singular: new TranslatableMarkup('card'),
  label_plural: new TranslatableMarkup('cards'),
  entity_keys: [
    'id' => 'id',
    'label' => 'title',
    'langcode' => 'langcode',
    'uuid' => 'uuid',
    'status' => 'status',
    'published' => "status",
    'owner' => "uid",
  ],
  handlers: [
    'views_data' => EntityViewsData::class,
    'storage' => CardStorage::class,
  ],
  links: [
    'canonical' => '/zz_card/{zz_card}',
    'delete-form' => '/zz_card/{zz_card}',
    'edit-form' => '/zz_card/{zz_card}/edit',
    'create' => '/zz_card',
  ],
  base_table: 'zz_card',
  data_table: 'zz_card_data',
  translatable: TRUE,
  show_revision_ui: TRUE,
)]
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

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = NULL, array $options = []) {
    // Cannot use parent logic, because this entity does not have canonical
    // URL template (or it doesn't make sense).
    return Url::fromRoute('zz_card.card', [
      'date' => $this->get('card_date')->getString(),
      'sign' => $this->get('sign')->getString(),
    ]);
  }

}
