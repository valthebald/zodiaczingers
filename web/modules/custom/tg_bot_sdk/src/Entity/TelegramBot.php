<?php

namespace Drupal\tg_bot_sdk\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Defines a RestResourceConfig configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "telegram_bot",
 *   label = @Translation("Telegram bot configuration"),
 *   label_collection = @Translation("Telegram bot configurations"),
 *   label_singular = @Translation("Telegram bot configuration"),
 *   label_plural = @Translation("Telegram bot configurations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Telegram bot configuration",
 *     plural = "@count Telegram bot configurations",
 *   ),
 *   config_prefix = "telegram_bot",
 *   admin_permission = "administer telegram bots",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   config_export = {
 *     "id",
 *     "plugin_id",
 *     "granularity",
 *     "configuration"
 *   },
 *   handlers = {
 *     "form" = {
 *       "add" = "Drupal\tg_bot_sdk\Form\TelegramBotEntityForm",
 *       "edit" = "Drupal\tg_bot_sdk\Form\TelegramBotEntityForm"
 *     },
 *     "list_builder" = "Drupal\Core\Config\Entity\ConfigEntityListBuilder"
 *   }
 * )
 */
class TelegramBot extends ConfigEntityBase {

  /**
   * Bot config id.
   *
   * @var string
   */
  protected string $id;

  /**
   * The REST resource plugin id.
   *
   * @var string
   */
  protected string $plugin_id;

  /**
   * The REST resource configuration.
   *
   * @var array|NULL
   */
  protected ?array $configuration;

  /**
   * The rest resource plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    // The config entity id looks like the plugin id but uses __ instead of :
    // because : is not valid for config entities.
    if (!isset($this->plugin_id) && isset($this->id)) {
      // Generate plugin_id on first entity creation.
      $this->plugin_id = str_replace('.', ':', $this->id);
    }
  }

  /**
   * Returns the resource plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected function getResourcePluginManager() {
    if (!isset($this->pluginManager)) {
      $this->pluginManager = \Drupal::service('plugin.manager.rest');
    }
    return $this->pluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getResourcePlugin() {
    return $this->getPluginCollections()['resource']->get($this->plugin_id);
  }

  /**
   * Retrieves a list of supported HTTP methods for this resource.
   *
   * @return string[]
   *   A list of supported HTTP methods.
   */
  protected function getMethodsForMethodGranularity() {
    $methods = array_keys($this->configuration);
    return array_map([$this, 'normalizeRestMethod'], $methods);
  }

  /**
   * Retrieves a list of supported authentication providers.
   *
   * @param string $method
   *   The request method e.g GET or POST.
   *
   * @return string[]
   *   A list of supported authentication provider IDs.
   */
  public function getAuthenticationProvidersForMethodGranularity($method) {
    $method = $this->normalizeRestMethod($method);
    if (in_array($method, $this->getMethods()) && isset($this->configuration[$method]['supported_auth'])) {
      return $this->configuration[$method]['supported_auth'];
    }
    return [];
  }

  /**
   * Retrieves a list of supported response formats.
   *
   * @param string $method
   *   The request method e.g GET or POST.
   *
   * @return string[]
   *   A list of supported format IDs.
   */
  protected function getFormatsForMethodGranularity($method) {
    $method = $this->normalizeRestMethod($method);
    if (in_array($method, $this->getMethods()) && isset($this->configuration[$method]['supported_formats'])) {
      return $this->configuration[$method]['supported_formats'];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'resource' => new DefaultSingleLazyPluginCollection($this->getResourcePluginManager(), $this->plugin_id, []),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    foreach ($this->getRestResourceDependencies()->calculateDependencies($this) as $type => $dependencies) {
      foreach ($dependencies as $dependency) {
        $this->addDependency($type, $dependency);
      }
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function onDependencyRemoval(array $dependencies) {
    $parent = parent::onDependencyRemoval($dependencies);

    // If the dependency problems are not marked as fixed at this point they
    // should be related to the resource plugin and the config entity should
    // be deleted.
    $changed = $this->getRestResourceDependencies()->onDependencyRemoval($this, $dependencies);
    return $parent || $changed;
  }

  /**
   * Returns the REST resource dependencies.
   *
   * @return \Drupal\rest\Entity\ConfigDependencies
   */
  protected function getRestResourceDependencies() {
    return \Drupal::service('class_resolver')->getInstanceFromDefinition(ConfigDependencies::class);
  }

  /**
   * Normalizes the method.
   *
   * @param string $method
   *   The request method.
   *
   * @return string
   *   The normalized request method.
   */
  protected function normalizeRestMethod($method) {
    return strtoupper($method);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    \Drupal::service('router.builder')->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    \Drupal::service('router.builder')->setRebuildNeeded();
  }

}
