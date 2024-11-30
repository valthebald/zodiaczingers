<?php

namespace Drupal\zz_card\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Limit response max-age for the "current day" responses.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  public function __construct(
    protected RouteMatchInterface $routeMatch,
    protected TimeInterface $time,
  ) {}

  /**
   * Limit max age for permanently-cacheable responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The Event to process.
   */
  public function onResponse(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }
    $response = $event->getResponse();
    if (!$response instanceof CacheableResponseInterface) {
      return;
    }
    $metadata = $response->getCacheableMetadata();
    if (!in_array('today', $metadata->getCacheContexts())) {
      return;
    }
    // Response should expire tomorrow.
    $requestTime = $this->time->getRequestTime();
    $tomorrowStart = 86400 + mktime(0, 0, 0, date('m', $requestTime),
      date('d', $requestTime), date('Y', $requestTime));
    $maxAgeTomorrow = $tomorrowStart - $requestTime;
    $currentMaxAge = $response->getMaxAge();
    if ($currentMaxAge === Cache::PERMANENT
      || ($currentMaxAge > $maxAgeTomorrow)) {
      $response->setMaxAge($maxAgeTomorrow);
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents(): array {
    // Run after Drupal\Core\EventSubscriber\FinishResponseSubscriber.
    return [KernelEvents::RESPONSE => [['onResponse', -100]]];
  }

}
