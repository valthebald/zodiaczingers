<?php

namespace Drupal\zz_card\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zz_card\Sign;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * @param Sign $sign
   * @param $date
   *
   * @return array
   */
  public function card($sign, $date = '') {
    $real = Sign::tryFrom($sign);
    if (!$real) {
      throw new NotFoundHttpException();
    }
    return [
      '#type' => 'component',
      '#component' => 'zz_card:card',
      '#props' => [
        'sign' => $sign,
        'icon' => $real->icon(),
        'title' => $real->name,
        'content' => $this->t('Hello world'),
      ],
      '#slots' => [],
    ];
  }

}
