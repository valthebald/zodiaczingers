<?php

namespace Drupal\zz_card;

/**
 * Zodiac signs enum.
 */
enum Sign : string {

  case Aries = 'aries';
  case Taurus = 'taurus';
  case Gemini = 'gemini';
  case Cancer = 'cancer';
  case Leo = 'leo';
  case Virgo = 'virgo';
  case Libra = 'libra';
  case Scorpio = 'scorpio';
  case Sagittarius = 'sagittarius';
  case Capricorn = 'capricorn';
  case Aquarius = 'aquarius';
  case Pisces = 'pisces';

  public function icon() : string {
    return match ($this) {
      self::Aries => '♈︎',
      self::Taurus => '♉︎',
      self::Gemini =>  '♊︎',
      self::Cancer => '♋︎',
      self::Leo => '♌',
      self::Virgo => '♍',
      self::Libra => '♎︎',
      self::Scorpio => '♏',
      self::Sagittarius => '︎︎♐',
      self::Capricorn => '︎︎♑',
      self::Aquarius => '♒',
      self::Pisces => '♓',
    };
  }


}
