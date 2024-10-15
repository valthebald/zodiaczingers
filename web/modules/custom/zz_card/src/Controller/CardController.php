<?php

namespace Drupal\zz_card\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zz_card\Sign;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * The horoscope card.
   *
   * @param Sign $sign
   *   Sign to show horoscope for.
   * @param string $date
   *
   * @return array
   *   Render array.
   */
  public function card($sign, $date = '') {
    $real = Sign::tryFrom($sign);
    if (!$real) {
      throw new NotFoundHttpException();
    }
    /** @var \Drupal\zz_card\HoroscopeGenerator $generator */
    $generator = \Drupal::service('zz_card.generator');
    $candidate = \Drupal::entityTypeManager()->getStorage('zz_card')
      ->loadBySignAndDate($real->value, (int) $date);
    if (!$candidate) {
      $content = $generator->generateHoroscope($real);
      $candidate = \Drupal::entityTypeManager()->getStorage('zz_card')
        ->create([
          'card_date' => (int) $date,
          'sign' => $real->value,
          'content' => $content,
        ]);
      $candidate->save();
    }
    return [
      '#cache' => [
        'tags' => $candidate->getCacheTags(),
      ],
      '#type' => 'component',
      '#component' => 'zz_card:card',
      '#props' => [
        'sign_name' => $sign,
        'icon' => $real->icon(),
        'content' => $candidate->get('content')->getValue()[0]['value'],
        'title' => $real->name,
      ],
      '#slots' => [],
    ];
  }

}
