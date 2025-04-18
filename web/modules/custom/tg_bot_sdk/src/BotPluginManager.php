<?php

namespace Drupal\tg_bot_sdk;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceInterface;

/**
 * Manages discovery and instantiation of bot plugins.
 *
 * @see plugin_api
 */
class BotPluginManager extends DefaultPluginManager {

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
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/rest/resource',
      $namespaces,
      $module_handler,
      ResourceInterface::class,
      RestResource::class,
      'Drupal\rest\Annotation\RestResource',
    );

    $this->setCacheBackend($cache_backend, 'rest_plugins');
    $this->alterInfo('rest_resource');
  }

}
