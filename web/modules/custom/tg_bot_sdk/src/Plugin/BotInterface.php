<?php

namespace Drupal\tg_bot_sdk\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Specifies the publicly available methods of a bot plugin.
 *
 * @see plugin_api
 *
 * @ingroup third_party
 */
interface BotInterface extends PluginInspectionInterface {

  /**
   * Returns the list of Bot plugins.
   *
   * @return array
   *   List of bot command names, keyed by command machine names.
   *   Examples: ['hello' => t('Hello'), 'start' => t('Start')].
   */
  public function commandList() : array;

}
