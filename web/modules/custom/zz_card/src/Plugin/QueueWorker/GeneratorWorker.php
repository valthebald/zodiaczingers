<?php

namespace Drupal\zz_card\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\State;
use Drupal\tsp_order\Entity\Order;
use Drupal\tsp_order\PaidOrderQueueItem;
use Drupal\zz_card\HoroscopeGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Horoscope-generating worker.
 *
 * @QueueWorker(
 *   id = "zz_card_generate",
 *   title = @Translation("Generate horoscope"),
 * )
 */
final class GeneratorWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  const QUEUE_NAME = 'zz_card_generate';

  protected HoroscopeGenerator $generator;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = new self($configuration, $plugin_id, $plugin_definition);

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function processItem($data): void {
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

}
