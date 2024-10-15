<?php

namespace Drupal\zz_card;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\zz_card\Entity\Card;

interface CardStorageInterface extends ContentEntityStorageInterface {

  /**
   * Try to load a horoscope entity given sign and date.
   *
   * @param string $sign
   *   The sign.
   * @param int $date
   *   The date in format YYMMDD.
   *
   * @return \Drupal\zz_card\Entity\Card|null
   */
  public function loadBySignAndDate(string $sign, int $date = 0): ?Card;
}
