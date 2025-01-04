<?php

namespace Drupal\tg_bot_sdk\Plugin\TelegramBot;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\tg_bot_sdk\Attribute\TelegramBot;
use Drupal\tg_bot_sdk\Plugin\BotInterface;

#[TelegramBot(
  id: "test",
  label: new TranslatableMarkup("Test"),
)]
class TestBot implements BotInterface {

  /**
   * @inheritDoc
   */
  public function commandList(): array {
    return [
      'test' => 'Test',
    ];
  }

  /**
   * @inheritDoc
   */
  public function getPluginId() {
    return 'test';
  }

  /**
   * @inheritDoc
   */
  public function getPluginDefinition() {
    return [];
  }

}
