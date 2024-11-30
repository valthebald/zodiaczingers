<?php

namespace Drupal\zz_card\Cache;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\Core\Cache\Context\RequestStackCacheContextBase;

/**
 * Defines the TodayCacheContext service, for "day"-level caching.
 *
 * Cache context ID: 'today'.
 */
class TodayCacheContext implements CacheContextInterface {

  /**
   * TodayCacheContext constructor.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(protected TimeInterface $time) {}

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Today');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() : string {
    return date('Ymd', $this->time->getRequestTime());
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    $metadata = new CacheableMetadata();
    $requestTime = $this->time->getRequestTime();
    $tomorrowStart = 86400 + mktime(0, 0, 0, date('m', $requestTime),
        date('d', $requestTime), date('Y', $requestTime));
    $metadata->setCacheMaxAge($tomorrowStart - $requestTime);
    $metadata->setCacheMaxAge(100);
    return $metadata;
  }

}
