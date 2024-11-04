<?php

namespace Drupal\zz_card\Controller;

use Drupal\ai_translate\TextTranslatorInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\zz_card\HoroscopeGenerator;
use Drupal\zz_card\Sign;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Horoscope card page controller.
 */
class CardController extends ControllerBase {

  use StringTranslationTrait;

  /**
   * That's stupid. Is there a "standard" way to get month name from its number?
   */
  const MONTH_NAMES = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
  ];

  public function __construct(
    protected TimeInterface $time,
    protected HoroscopeGenerator $horoscopeGenerator,
    protected TextTranslatorInterface $textTranslator,
    protected Renderer $renderer,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
      $container->get('zz_card.generator'),
      $container->get('ai_translate.text_translator'),
      $container->get('renderer'),
    );
  }

  /**
   * The horoscope card.
   *
   * @param string $sign
   *   Sign to show horoscope for.
   * @param string $date
   *
   * @return array
   *   Render array.
   */
  public function card(string $sign, $date = '') {
    $real = ('auto' === $sign) ? Sign::fromDate($this->time->getCurrentTime())
      : Sign::tryFrom($sign);
    if (!$real) {
      throw new NotFoundHttpException();
    }
    $dateGuess = empty($date);
    if (!$date) {
      $date = date('Ymd');
    }
    $displayedDate = \DateTime::createFromFormat('Ymd', $date)
      ->getTimestamp();
    $currentLanguage = $this->languageManager()->getCurrentLanguage();
    $build = [
      // By having zz_card_list as a cache tag, we ensure that the page will
      // be rebuilt when the card is in database, so that we can save one AJAX
      // call.
      '#cache' => [
        'tags' => [],
      ],
      '#type' => 'component',
      '#component' => 'zz_card:card',
      '#props' => [
        'sign_name' => $sign,
        'icon' => $real->icon(),
        'title' => $real->name,
      ],
      '#slots' => [
        'header' => [
          '#type' => 'html_tag',
          '#tag' => 'h1',
          '#attributes' => ['class' => ['card-header']],
          '#value' => $this->formatDate($displayedDate),
        ],
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
      ],
    ];
    $cardStorage = $this->entityTypeManager()->getStorage('zz_card');

    // Should we display previous link?
    $previous = $cardStorage->loadBySignAndDate($sign,
      (int) date('Ymd',$displayedDate - 86400));
    if (!empty($previous)) {
      $build['#slots']['date_links']['prev'] = [
        '#type' => 'link',
        '#rel' => 'prev',
        '#title' => $this->t('Previous'),
        '#url' => Url::fromRoute('zz_card.card', [
          'sign' => $sign,
          'date' => date('Ymd', $displayedDate - 86400),
        ]),
      ];
    }
    // Should we display next link?
    $next = $cardStorage->loadBySignAndDate($sign,
      (int) date('Ymd',$displayedDate + 86400));
    if (!empty($next)) {
      $build['#slots']['date_links']['next'] = [
        '#type' => 'link',
        '#rel' => 'next',
        '#title' => $this->t('Next'),
        '#url' => Url::fromRoute('zz_card.card', [
          'sign' => $sign,
          'date' => date('Ymd', $displayedDate + 86400),
        ]),
      ];
    }

    $card = \Drupal::entityTypeManager()->getStorage('zz_card')
      ->loadBySignAndDate($real->value, (int) $date);
    if (!$card) {
      if ($date > 0 && $date < date('Ymd')) {
        // Cannot generate horoscopes for the past dates.
        throw new NotFoundHttpException();
      }
      $build['#cache']['tags'][] = 'zz_card_list';
      return $build;
    }
    if ($card->hasTranslation($currentLanguage->getId())) {
      $card = $card->getTranslation($currentLanguage->getId());
    }
    else {
      $build['#cache']['tags'] = $card->getCacheTags();
      if ($dateGuess) {
        $build['#cache']['tags'][] = 'zz_card_list';
      }
      return $build;
    }

    unset($build['#attached']);
    $build['#cache'] = [
      'max-age' => Cache::PERMANENT,
      'tags' => $card->getCacheTags(),
    ];
    if ($dateGuess) {
      $build['#cache']['tags'][] = 'zz_card_list';
    }
    $build['#slots']['content'] = [
      '#type' => 'processed_text',
      '#text' => $card->get('content')->value,
    ];
    return $build;
  }

  /**
   * The horoscope card.
   *
   * @param string $sign
   *   Sign to show horoscope for.
   * @param string $date
   *
   * @return array
   *   Render array.
   */
  public function cardList($date = '') {
    $dateGuess = empty($date);
    if (!$date) {
      $date = date('Ymd');
    }
    $displayedDate = \DateTime::createFromFormat('Ymd', $date)
      ->getTimestamp();
    $currentSign = Sign::fromDate($displayedDate);

    $build = [
      // By having zz_card_list as a cache tag, we ensure that the page will
      // be rebuilt when the card is in database, so that we can save one AJAX
      // call.
      '#cache' => [
        'tags' => [],
      ],
      '#theme' => 'item_list',
      '#title' => $this->formatDate($displayedDate),
      '#attributes' => [
        'class' => ['item-list', 'sign-list'],
      ],
      '#items' => [],
    ];
    $linkDefault = [
      '#type' => 'link',
      '#url' =>  Url::fromRoute('zz_card.card', [
        'date' => $date,
      ]),
    ];
    foreach (Sign::cases() as $sign) {
      $dates = $sign->getDates();
      $link = [
        '#wrapper_attributes' => ['class' => []],
        '#title' => $sign->icon() . ' ' . $this->t($sign->name) . ' (' .
          $this->t(self::MONTH_NAMES[$dates['startMonth']]) . ' ' . $dates['startDay'] . ' - ' .
          $this->t(self::MONTH_NAMES[$dates['endMonth']]) . ' ' . $dates['endDay'] . ')',
      ] + $linkDefault;
      $link['#url']->setRouteParameter('sign', $sign->value);
      if ($sign->value === $currentSign->value) {
        $link['#wrapper_attributes']['class'][] = 'active';
      }
      $build['#items'][] = $link;
    }
    if ($dateGuess) {
      $build['#cache']['tags'][] = 'zz_card_list';
    }
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

  protected function formatDate (int $timestamp) : string {
    $weekday = $this->t(date('l', $timestamp));
    $month = $this->t(date('F', $timestamp));
    $weekday = $this->t(date('l', $timestamp));
    $month = $this->t(date('F', $timestamp));
    return $this->t('Horoscope for @weekday', ['@weekday' => $weekday])
      . ', ' . date('d.m.Y', $timestamp);
    return $this->t('Horoscope for @weekday, @month @day, @year', [
      '@weekday' => $weekday,
      '@month' => $month,
      '@day' => date('j', $timestamp),
      '@year' => date('Y', $timestamp),
    ]);
  }

}
