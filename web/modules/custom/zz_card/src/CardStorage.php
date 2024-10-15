<?php

namespace Drupal\zz_card;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\zz_card\Entity\Card;

class CardStorage extends SqlContentEntityStorage implements CardStorageInterface {

  /**
   * {@inheritDoc}
   */
  public function create(array $values = []) {
    if (empty($values['card_date'])) {
      $values['card_date'] = (int) date('Ymd');
    }
    return parent::create($values);
  }

  /**
   * {@inheritDoc}
   */
  public function loadBySignAndDate(string $sign, int $date = 0): ?Card {
    if (!$date) {
      $date = (int) date('Ymd');
    }
    $ids = $this->getQuery()->accessCheck(FALSE)
      ->condition('sign', $sign)
      ->condition('card_date', $date)
      ->execute();
    if (!$ids) {
      return NULL;
    }
    $candidates = $this->loadMultiple($ids);
    return $candidates ? current($candidates) : NULL;
  }

}
