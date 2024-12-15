<?php

namespace Drupal\tg_bot_sdk\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Telegram bot annotation object.
 *
 * Plugin Namespace: Plugin\tg_bot_sdk\bot
 *
 * @see plugin_api
 *
 * @ingroup third_party
 *
 * @Annotation
 */
class TelegramBot extends Plugin {

  /**
   * Bot plugin ID.
   *
   * @var string
   */
  public string $id;

  /**
   * The human-readable name of the REST resource plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Stringable
   */
  public \Stringable $label;

}
