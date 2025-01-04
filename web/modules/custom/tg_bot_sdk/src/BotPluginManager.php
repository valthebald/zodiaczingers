<?php

namespace Drupal\tg_bot_sdk;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\tg_bot_sdk\Attribute\TelegramBot;
use Drupal\tg_bot_sdk\Plugin\BotInterface;
use Telegram\Bot\BotsManager;

/**
 * Manages discovery and instantiation of bot plugins.
 *
 * @see plugin_api
 */
class BotPluginManager extends DefaultPluginManager implements BotPluginManagerInterface {

  /**
   * Constructs a new \Drupal\rest\Plugin\Type\ResourcePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct(
      'Plugin/TelegramBot',
      $namespaces,
      $module_handler,
      BotInterface::class,
      TelegramBot::class,
      '\Drupal\tg_bot_sdk\Annotation\TelegramBot',
    );

    $this->setCacheBackend($cache_backend, 'tg_bot_plugin');
    $this->alterInfo('tg_bot_plugin');
  }

  /**
   * @inheritDoc
   */
  public function announceBots(): bool {
    $config = [];
    foreach ($this->entityTypeManager->getStorage('telegram_bot')->loadMultiple() as $entity) {
      $config['bots'][$entity->id()] = $entity->label();
    }
    if (empty($config['bots'])) {
      return TRUE;
    }
    $botManager = new BotsManager($config);
    $botManager;
  }

}
