<?php

declare(strict_types=1);

namespace Drupal\omnipedia_discourse\Service;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\omnipedia_discourse\Service\DiscourseConfigInterface;

/**
 * The Discourse configuration service.
 */
class DiscourseConfig implements DiscourseConfigInterface {

  /**
   * The Discourse SSO module configuration name.
   */
  protected const DISCOURSE_SSO_CONFIG_NAME = 'discourse_sso.settings';

  /**
   * Service constructor; saves dependencies.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The Drupal configuration object factory service.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getConfig(bool $editable = false): Config {

    if ($editable === false) {
      return $this->configFactory->get(self::DISCOURSE_SSO_CONFIG_NAME);
    }

    return $this->configFactory->getEditable(self::DISCOURSE_SSO_CONFIG_NAME);

  }

  /**
   * {@inheritdoc}
   */
  public function getConfigName(): string {
    return self::DISCOURSE_SSO_CONFIG_NAME;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigCacheTags(): array {
    return ['config:' . self::DISCOURSE_SSO_CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getServerUrl(): string {
    return $this->getConfig()->get('discourse_server');
  }

}
