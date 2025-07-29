<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Plugin\Menu;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;
use Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents a menu link to the configured Discourse server.
 *
 * @todo Don't output if the user doesn't have the 'access discourse sso'
 *   permission.
 */
class DiscourseMenuLink extends MenuLinkDefault {

  /**
   * Our menu link configuration name.
   */
  protected const MENU_LINK_CONFIG_NAME = 'omnipedia_discourse.settings';

  /**
   * {@inheritdoc}
   */
  protected $overrideAllowed = [
    'menu_name'   => 1,
    'parent'      => 1,
    'weight'      => 1,
    'expanded'    => 1,
    'enabled'     => 1,
  ];

  /**
   * Plug-in constructor; saves dependencies
   *
   * @param array $configuration
   *   A configuration array containing information about the plug-in instance.
   *
   * @param string $pluginId
   *   The plugin_id for the plug-in instance.
   *
   * @param mixed $pluginDefinition
   *   The plug-in implementation definition.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscourseConfigInterface $discourseConfig
   *   The Discourse configuration service.
   *
   * @param \Drupal\omnipedia_discourse\Service\DiscoursePermalinkResolverInterface $discoursePermalinkResolver
   *   The Discourse permalink resolver service.
   *
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $staticOverride
   *   The Drupal static override storage.
   */
  public function __construct(
    array $configuration, $pluginId, $pluginDefinition,
    protected readonly ConfigFactoryInterface $configFactory,
    protected readonly DiscourseConfigInterface $discourseConfig,
    protected readonly DiscoursePermalinkResolverInterface $discoursePermalinkResolver,
    StaticMenuLinkOverridesInterface $staticOverride,
  ) {

    parent::__construct(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $staticOverride,
    );

  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration, $pluginId, $pluginDefinition,
  ) {

    return new static(
      $configuration, $pluginId, $pluginDefinition,
      $container->get('config.factory'),
      $container->get(DiscourseConfigInterface::class),
      $container->get(DiscoursePermalinkResolverInterface::class),
      $container->get('menu_link.static.overrides'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getUrlObject($title_attribute = true) {

    try {

      $url = $this->discoursePermalinkResolver->fromDate();

    // If there's any error thrown by the above, fall back to using the server
    // URL without a permalink.
    } catch (\Error|\Exception $exception) {

      $this->pluginDefinition['url'] = $this->discourseConfig->getServerUrl();

      return parent::getUrlObject($title_attribute);

    }

    $options = $this->getOptions() + $url->getOptions();

    if ($title_attribute && $description = $this->getDescription()) {
      $options['attributes']['title'] = $description;
    }

    $url->setOptions($options);

    return $url;

  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return (string) $this->configFactory->get(
      self::MENU_LINK_CONFIG_NAME,
    )->get('menu_link_text');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {

    return Cache::mergeContexts(parent::getCacheContexts(), [
      // The permalink varies by date.
      'omnipedia_dates',
      'user.permissions',
    ]);

  }

  /**
   * {@inheritdoc}
   *
   * @todo Also get cache tags from the permalink resolver, including the date
   *   this is cached for, etc.
   */
  public function getCacheTags() {

    return Cache::mergeTags(
      parent::getCacheTags(),
      $this->discourseConfig->getConfigCacheTags(),
      ['config:' . self::MENU_LINK_CONFIG_NAME],
    );

  }

}
