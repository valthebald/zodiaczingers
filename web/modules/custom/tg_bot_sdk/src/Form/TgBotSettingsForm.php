<?php

namespace Drupal\tg_bot_sdk\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Telegram Bot settings.
 */
class TgBotSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   */
  const CONFIG_NAME = 'tg_bot_sdk.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tg_bot_sdk_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::CONFIG_NAME,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['processing_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Processing mode'),
      '#options' => [
        'poll' => $this->t('Poll'),
        'webhook' => $this->t('Webhook'),
      ],
      '#config_target' => static::CONFIG_NAME . ':processing_mode',
      '#required' => TRUE,
      '#description' => $this->t('To use webhooks, website must be accessible by Telegram servers.'),
    ];
    return parent::buildForm($form, $form_state);
  }

}
