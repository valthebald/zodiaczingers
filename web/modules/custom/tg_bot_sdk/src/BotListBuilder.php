<?php

namespace Drupal\tg_bot_sdk;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class BotListBuilder extends ConfigEntityListBuilder {



  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'label' => $this->t('Title'),
      'status' => $this->t('Status'),
    ];
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
      '#url' => $entity->toUrl(),
    ];
    $row['status'] = $entity->get('status') ? $this->t('published') : $this->t('not published');
    return $row;
  }

}
