<?php

namespace Drupal\zz_card\Controller;

use Drupal\ai_translate\TextTranslatorInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zz_card\HoroscopeGenerator;
use Drupal\zz_card\Sign;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends ControllerBase {

  use StringTranslationTrait;

  public function __construct(
    protected HoroscopeGenerator $horoscopeGenerator,
    protected TextTranslatorInterface $textTranslator,
    protected Renderer $renderer,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('zz_card.generator'),
      $container->get('ai_translate.text_translator'),
      $container->get('renderer'),
    );
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
    if (!$date) {
      $date = date('Ymd');
    }
    $currentLanguage = $this->languageManager()->getCurrentLanguage();
    $build = [
      // By having zz_card_list as a cache tag, we ensure that the page will
      // be rebuilt when the card is in database, so that we can save one AJAX
      // call.
      '#cache' => [
        'tags' => ['zz_card_list']
      ],
      '#type' => 'component',
      '#component' => 'zz_card:card',
      '#props' => [
        'sign_name' => $sign,
        'icon' => $real->icon(),
        'title' => $real->name,
      ],
      '#slots' => [
        'content' => [
          '#type' => 'container',
          '#markup' => $this->t('The stars are aligning, please wait...'),
          '#attributes' => [
            'data-card' => $real->value,
            'data-date' => $date,
          ],
        ],
      ],
      '#attached' => [
        'library' => ['zz_card/card_fetcher'],
      ]
    ];
    $card = \Drupal::entityTypeManager()->getStorage('zz_card')
      ->loadBySignAndDate($real->value, (int) $date);
    if (!$card) {
      if ($date > 0 && $date < date('Ymd')) {
        // Cannot generate horoscopes for the past dates.
        throw new NotFoundHttpException();
      }
      return $build;
    }
    if ($card->hasTranslation($currentLanguage->getId())) {
      $card = $card->getTranslation($currentLanguage->getId());
    }
    else {
      $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'],
        $card->getCacheTags());
      return $build;
    }

    unset($build['#attached']);
    $build['#cache'] = [
      'max-age' => Cache::PERMANENT,
      'tags' => $card->getCacheTags(),
    ];
    $build['#slots']['content'] = [
      '#type' => 'processed_text',
      '#text' => $card->get('content')->value,
    ];
    return $build;
  }

  /**
   * AJAX callback to check the card generation status.
   *
   * When the card is generated and translated to requested language,
   * Return its contents.
   *
   * @param Sign $sign
   *   Sign to show horoscope for.
   * @param string $date
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function cardLoadWhenReady($sign, $date) : JsonResponse {
    $real = Sign::tryFrom($sign);
    if (!$real) {
      throw new NotFoundHttpException();
    }
    $card = \Drupal::entityTypeManager()->getStorage('zz_card')
      ->loadBySignAndDate($real->value, (int) $date);
    if (!$card) {
      if ($date > 0 && $date < date('Ymd')) {
        // Cannot generate horoscopes for the past dates.
        throw new NotFoundHttpException();
      }
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
    $content = $card->get('content')->value;
    $currentLanguage = $this->languageManager()->getCurrentLanguage();
    if ($card->hasTranslation($currentLanguage->getId())) {
      $card = $card->getTranslation($currentLanguage->getId());
    }
    else {
      $card = $card->addTranslation($currentLanguage->getId());
      $card->set('content', $this->textTranslator->translateContent(
        $content, $currentLanguage, $this->languageManager()->getDefaultLanguage()
      ));
      $card->save();
    }
    $render = [
      '#type' => 'processed_text',
      '#text' => $card->get('content')->value,
    ];
    $response = new CacheableJsonResponse($this->renderer->render($render));
    $response->addCacheableDependency($card);
    $response->addCacheableDependency($currentLanguage);
    return $response;
  }

}
