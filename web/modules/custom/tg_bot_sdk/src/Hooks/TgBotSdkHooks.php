<?php

namespace Drupal\tg_bot_sdk\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Telegram\Bot\BotsManager;

/**
 * Hook implementations for Telegram Bot SDK.
 */
class TgBotSdkHooks {

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron(): void {
    $etm = \Drupal::entityTypeManager();
    $config = [];
    foreach ($etm->getStorage('telegram_bot')->loadMultiple() as $telegram_bot) {
      $config['bots'][$telegram_bot->id()] = $telegram_bot->label();
    }
    $botManager = new BotsManager($config);
    foreach ($config['bots'] as $bot_id => $bot) {
      $updates = $botManager->getUpdates($bot_id);
    }
  }

}
