<?php

namespace Drupal\zz_card\Controller;

use Drupal\ai_translate\TextTranslatorInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\zz_card\HoroscopeGenerator;
use Drupal\zz_card\Sign;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CardController extends ControllerBase {

  use StringTranslationTrait;

  public function __construct(
    protected HoroscopeGenerator $horoscopeGenerator,
    protected TextTranslatorInterface $textTranslator,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('zz_card.generator'),
      $container->get('ai_translate.text_translator'),
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

    return [
      '#cache' => [
        'tags' => $card->getCacheTags(),
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
          '#type' => 'processed_text',
          '#text' => $card->get('content')->value,
        ],
      ],
    ];
  }

}
