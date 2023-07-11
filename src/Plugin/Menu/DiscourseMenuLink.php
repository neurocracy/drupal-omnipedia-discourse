<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Plugin\Menu;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents a menu link to the configured Discourse server.
 *
 * @todo Don't output if the user doesn't have the 'access discourse sso'
 *   permission.
 */
class DiscourseMenuLink extends MenuLinkDefault {

  /**
   * The Discourse SSO module configuration name.
   */
  protected const DISCOURSE_SSO_CONFIG_NAME = 'discourse_sso.settings';

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
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $staticOverride
   *   The Drupal static override storage.
   */
  public function __construct(
    array $configuration, $pluginId, $pluginDefinition,
    protected readonly ConfigFactoryInterface $configFactory,
    StaticMenuLinkOverridesInterface $staticOverride,
  ) {

    /** @var string|null */
    $url = $this->configFactory->get(
      self::DISCOURSE_SSO_CONFIG_NAME
    )->get('discourse_server');

    // If the Discourse server URL is set, this instructs
    if (!empty($url)) {
      $pluginDefinition['url'] = $url;
    }

    $pluginDefinition['title'] = $this->configFactory->get(
      self::MENU_LINK_CONFIG_NAME
    )->get('menu_link_text');

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
      $container->get('menu_link.static.overrides'),
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {

    return Cache::mergeContexts(parent::getCacheContexts(), [
      'user.permissions',
    ]);

  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {

    return Cache::mergeTags(
      parent::getCacheTags(),
      [
        'config:' . self::DISCOURSE_SSO_CONFIG_NAME,
        'config:' . self::MENU_LINK_CONFIG_NAME,
      ],
    );

  }

}
