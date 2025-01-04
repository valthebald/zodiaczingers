<?php

namespace Drupal\tg_bot_sdk\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\tg_bot_sdk\BotListBuilder;
use Drupal\tg_bot_sdk\Form\TelegramBotEntityForm;

/**
 * Defines a Telegram bot configuration entity class.
 */
#[ConfigEntityType(
  id: 'telegram_bot',
  label: new TranslatableMarkup("Telegram bot configuration"),
  label_collection: new TranslatableMarkup('Telegram bots'),
  label_singular: new TranslatableMarkup('Telegram bot'),
  label_plural: new TranslatableMarkup('Telegram bots'),
  config_prefix: 'telegram_bot',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'status' => 'status',
  ],
  handlers: [
    'form' => [
      'add' => TelegramBotEntityForm::class,
      'edit' => TelegramBotEntityForm::class,
    ],
    'list_builder' => BotListBuilder::class
  ],
  links: [
    'edit-form' => '/admin/config/services/tg_bot/edit/{telegram_bot}',
  ],
  admin_permission: 'administer telegram bots',
  config_export: [
    "id",
    "bot_id",
    "plugin_id",
    "label",
    "api_token",
    "status",
  ],
)]
class TelegramBot extends ConfigEntityBase {

  /**
   * Bot config id.
   *
   * @var string
   */
  protected string $id;

  /**
   * The API token.
   *
   * @var string
   */
  protected string $api_token;

}
