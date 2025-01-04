<?php

namespace Drupal\tg_bot_sdk\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tg_bot_sdk\BotPluginManagerInterface;
use Drupal\tg_bot_sdk\Entity\TelegramBot;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a telegram bot entity form.
 */
class TelegramBotEntityForm extends EntityForm {

  protected BotPluginManagerInterface $botPluginManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->botPluginManager = $container->get('plugin.manager.tg_bot');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $plugins = $this->botPluginManager->getDefinitions();
    foreach ($plugins as $pluginId => $pluginDefinition) {
      $options[$pluginId] = $pluginDefinition['label'];
    }
    $form['label'] = [
      '#title' => $this->t('Bot label'),
      '#type' => 'textfield',
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('The human-readable name for the bot.'),
      '#required' => TRUE,
      '#size' => 30,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$this->entity->isNew(),
      '#machine_name' => [
        'exists' => [TelegramBot::class, 'load'],
        'source' => ['label'],
      ],
      '#description' => $this->t('Unique machine-readable name: lowercase letters, numbers, and underscores only.', [
        '%node-add' => $this->t('Add content'),
      ]),
    ];
    $form['bot_id'] = [
      '#type' =>  'textfield',
      '#title' => $this->t('Bot username'),
      '#description' => $this->t('Bot username, i.e. what comes after t.me in the URL, i.e. t.me/BotUsername'),
      '#default_value' => $this->entity->get('bot_id'),
    ];
    $form['plugin_id'] = [
      '#type' =>  'select',
      '#title' => $this->t('Plugin'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $this->entity->get('plugin_id'),
    ];
    $form['api_token'] = [
      '#type' => 'key_select',
      '#title' => $this->t('API token'),
      '#description' => $this->t('The API token. Read <a href="https://core.telegram.org/bots/tutorial#obtain-your-bot-token">instructions</a> on how to obtain a token.'),
      '#default_value' => $this->entity->get('api_token'),
    ];
    return $form;
  }

}
