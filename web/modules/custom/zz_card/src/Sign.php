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
      self::Leo => '♌︎',
      self::Virgo => '♍︎',
      self::Libra => '♎︎',
      self::Scorpio => '♏︎',
      self::Sagittarius => '︎︎♐︎',
      self::Capricorn => '︎︎♑︎',
      self::Aquarius => '♒︎',
      self::Pisces => '♓︎',
    };
  }

  /**
   * Start and end dates of a sign.
   *
   * @return string[]
   *   Start and end dates in format "F d".
   */
  public function getDates() : array {
    return match ($this) {
      self::Aries => [
        'startMonth' => 3,
        'startDay' => 21,
        'endMonth' => 4,
        'endDay' => 19,
        ],
      self::Taurus => [
        'startMonth' => 4,
        'startDay' => 20,
        'endMonth' => 5,
        'endDay' => 20,
      ],
      self::Gemini => [
        'startMonth' => 5,
        'startDay' => 21,
        'endMonth' => 6,
        'endDay' => 21,
      ],
      self::Cancer => [
        'startMonth' => 6,
        'startDay' => 22,
        'endMonth' => 7,
        'endDay' => 22,
      ],
      self::Leo => [
        'startMonth' => 7,
        'startDay' => 23,
        'endMonth' => 8,
        'endDay' => 22,
      ],
      self::Virgo => [
        'startMonth' => 8,
        'startDay' => 23,
        'endMonth' => 9,
        'endDay' => 22,
      ],
      self::Libra => [
        'startMonth' => 9,
        'startDay' => 23,
        'endMonth' => 10,
        'endDay' => 23,
      ],
      self::Scorpio => [
        'startMonth' => 10,
        'startDay' => 24,
        'endMonth' => 11,
        'endDay' => 21,
      ],
      self::Sagittarius => [
        'startMonth' => 11,
        'startDay' => 22,
        'endMonth' => 12,
        'endDay' => 21,
      ],
      self::Capricorn => [
        'startMonth' => 12,
        'startDay' => 22,
        'endMonth' => 1,
        'endDay' => 19,
      ],
      self::Aquarius => [
        'startMonth' => 1,
        'startDay' => 20,
        'endMonth' => 2,
        'endDay' => 18,
      ],
      self::Pisces => [
        'startMonth' => 2,
        'startDay' => 19,
        'endMonth' => 3,
        'endDay' => 20,
      ],
    };
  }

  /**
   * Returns Sign given a timestamp.
   *
   * @param int $timestamp
   *
   * @return self
   */
  public static function fromDate(int $timestamp) : self {
    $monthDate = (int) date('nd', $timestamp);
    return match (TRUE) {
      // January 20 to February 18.
      ($monthDate >= 120 && $monthDate < 219) => self::Aquarius,
      // February 19 to March 20.
      ($monthDate >= 219 && $monthDate < 321) => self::Pisces,
      // March 21 to April 19.
      ($monthDate >= 321 && $monthDate < 420) => self::Aries,
      // April 20 to May 20.
      ($monthDate >= 420 && $monthDate < 521) => self::Taurus,
      // May 21 to June 21.
      ($monthDate >= 521 && $monthDate < 622) => self::Gemini,
      // June 22 to July 22.
      ($monthDate >= 622 && $monthDate < 723) => self::Cancer,
      // July 23 to August 22.
      ($monthDate >= 723 && $monthDate < 823) => self::Leo,
      // August 23 to September 22.
      ($monthDate >= 823 && $monthDate < 923) => self::Virgo,
      // September 23 to October 23.
      ($monthDate >= 923 && $monthDate < 1024) => self::Libra,
      // October 24 to November 21.
      ($monthDate >= 1024 && $monthDate < 1122) => self::Scorpio,
      // November 22 to December 21.
      ($monthDate >= 1122 && $monthDate < 1222) => self::Sagittarius,
      // December 22 to January 19
      default => self::Capricorn,
    };
  }

}
