<?php

namespace Drupal\tg_bot_sdk;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Manages discovery and instantiation of bot plugins.
 *
 * @see plugin_api
 */
interface BotPluginManagerInterface extends
  PluginManagerInterface,
  CachedDiscoveryInterface,
  CacheableDependencyInterface {

  /**
   * Registers all bots with Telegram API.
   *
   * @return bool
   */
  public function announceBots() : bool;

}
