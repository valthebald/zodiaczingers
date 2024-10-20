<?php

namespace Drupal\zz_card\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zz_card\HoroscopeGenerator;
use Drupal\zz_card\Sign;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends ControllerBase {

  use StringTranslationTrait;

  public function __construct(
    protected HoroscopeGenerator $horoscopeGenerator) { }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('zz_card.generator'));
  }

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
    $card = \Drupal::entityTypeManager()->getStorage('zz_card')
      ->loadBySignAndDate($real->value, (int) $date);
    if (!$card) {
      $content = $this->horoscopeGenerator->generateHoroscope($real);
      $cardStorage = $this->entityTypeManager()
        ->getStorage('zz_card');
      $card = $cardStorage->create([
        'card_date' => (int) $date,
        'sign' => $real->value,
        'content' => $content,
      ]);
      $cardStorage->save($card);
    }
    return [
      '#cache' => [
        'tags' => $card->getCacheTags(),
      ],
      '#type' => 'component',
      '#component' => 'zz_card:card',
      '#props' => [
        'sign_name' => $sign,
        'icon' => $real->icon(),
        'content' => $card->get('content')->getValue()[0]['value'],
        'title' => $real->name,
      ],
      '#slots' => [],
    ];
  }

}
