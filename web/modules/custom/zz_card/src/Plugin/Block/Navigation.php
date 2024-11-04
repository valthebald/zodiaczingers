<?php

namespace Drupal\zz_card\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Block\BlockPluginTrait;
use Drupal\Core\Cache\CacheableDependencyTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\zz_card\Sign;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines sign and date selection block.
 */
#[Block(
  id: "zz_navigation",
  admin_label: new TranslatableMarkup("ZodiacZingers: Navigation"),
  category: new TranslatableMarkup("Block")
)]
class Navigation extends PluginBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  use BlockPluginTrait;
  use CacheableDependencyTrait;

  /**
   * Creates a Broken Block instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
    protected AccountInterface $currentUser,
    protected CurrentRouteMatch $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Navigation block makes sense only on card pages.
    if ('zz_card.card' !== $this->routeMatch->getRouteName()) {
      return [];
    }
    $cardDate = $this->routeMatch->getParameter('date');
    $cardDate = $cardDate ? \DateTime::createFromFormat('Ymd', $cardDate)
      : new \DateTime();
    $sign = $this->routeMatch->getParameter('sign');
    $sign = ('auto' ===  $sign) ? Sign::fromDate($cardDate->getTimestamp())
      : Sign::tryFrom($sign);
    return [];
    $build = [
      '#type' => 'component',
      '#component' => 'zz_card:card-header',
      '#context' => [
        'date' => date('l, M d, Y', $cardDate->getTimestamp()),
        'sign' => $sign->name,
      ],
    ];
    /*$build2 = [
      '#type' => 'component',
      '#component' => 'zz_card:slider',
      '#slots' => ['slides' => []],
      '#attributes' => [
        'class' => ['card'],
      ],
    ];
    foreach (Sign::cases() as $sign) {
      $build2['#slots']['slides'][] = [
        '#type' => 'link',
        '#title' => $sign->icon(),
        '#attributes' => ['alt' => $sign->name],
        '#url' => Url::fromRoute('zz_card.card',
          ['sign' => $sign->value],
        ),
      ];
    }*/
    return $build;
  }

}
