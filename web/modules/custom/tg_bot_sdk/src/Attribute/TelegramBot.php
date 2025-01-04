<?php

declare(strict_types=1);

namespace Drupal\tg_bot_sdk\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a Telegram bot attribute object.
 *
 * Plugin Namespace: Plugin\tg_bot_sdk\resource
 *
 * For a working example, see \Drupal\tg_bot_sdk\Plugin\TelegramBot\TestBot
 *
 * @see plugin_api
 *
 * @ingroup third_party
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class TelegramBot extends Plugin {

  /**
   * Constructs a Telegram bot attribute.
   *
   * @param string $id
   *   The REST resource plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The human-readable name of the REST resource plugin.
   *
   * @see \Symfony\Component\Serializer\SerializerInterface
   * @see core/core.link_relation_types.yml
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
